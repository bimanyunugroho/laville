import { ClosedPeriod, ClosedPeriodApproval, ClosedPeriodPageProps, ClosedPeriodPagination, PaginationLink } from '@/types/closed_period';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';

interface ClosedPeriodState {
    closed_periods: ClosedPeriodPagination;
    searchQuery: string;
    monthFilter: string;
    yearFilter: string;
    isLoading: boolean;
}

const toast = useToast();

export const useClosedPeriodStore = defineStore('closed_period', {
    state: (): ClosedPeriodState => ({
        closed_periods: {
            data: [] as ClosedPeriod[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        monthFilter: '',
        yearFilter: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: ClosedPeriodPageProps) {
            this.closed_periods = props.closed_periods;

            // Initialize filters from URL params if they exist
            const urlParams = new URL(window.location.href).searchParams;
            this.searchQuery = urlParams.get('search') || '';
            this.monthFilter = urlParams.get('month') || '';
            this.yearFilter = urlParams.get('year') || '';
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debouncedSearch();
        },

        setMonthFilter(month: string) {
            this.monthFilter = month;
            this.debouncedSearch();
        },

        setYearFilter(year: string) {
            this.yearFilter = year;
            this.debouncedSearch();
        },

        debouncedSearch: debounce(function (this: any) {
            this.isLoading = true;
            router.get(
                route('admin.account.closed_period.index'),
                {
                    search: this.searchQuery,
                    month: this.monthFilter,
                    year: this.yearFilter,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => {
                        this.isLoading = false;
                    },
                    onError: () => {
                        this.isLoading = false;
                    },
                },
            );
        }, 300),

        resetFilters() {
            this.searchQuery = '';
            this.monthFilter = '';
            this.yearFilter = '';
            this.debouncedSearch();
        },

        goToPage(url: string) {
            if (!url) return;

            this.isLoading = true;

            // Extract existing URL parameters and add our filters
            const urlObj = new URL(url);
            if (this.searchQuery) urlObj.searchParams.set('search', this.searchQuery);
            if (this.monthFilter) urlObj.searchParams.set('month', this.monthFilter);
            if (this.yearFilter) urlObj.searchParams.set('year', this.yearFilter);

            router.visit(urlObj.toString(), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    this.isLoading = false;
                },
                onError: () => {
                    this.isLoading = false;
                },
            });
        },

        createClosedPeriode(closed_period: Partial<ClosedPeriod>) {
            this.isLoading = true;

            const formatDateWithCurrentTime = (dateString: string | undefined | null) => {
                if (!dateString) return null;
                const inputDate = new Date(dateString);
                const now = new Date();
                inputDate.setHours(now.getHours());
                inputDate.setMinutes(now.getMinutes());
                inputDate.setSeconds(now.getSeconds());
                return inputDate.toISOString().slice(0, 19).replace('T', ' ');
            };

            const formData = new FormData();

            const payload = {
                ...closed_period,
                user_id: closed_period.user_id ?? closed_period.user?.id,
                user_ack_id: closed_period.user_ack_id ?? null,
                user_reject_id: closed_period.user_reject_id ?? null,
                closed_date: formatDateWithCurrentTime(closed_period.closed_date),
            };

            delete payload.user;
            delete payload.userAck;
            delete payload.userReject;

            Object.entries(payload).forEach(([key, value]) => {
                if (value !== undefined) {
                    let formattedValue = value;

                    if (typeof value === 'boolean') {
                        formattedValue = value ? '1' : '0';
                    } else if (value === null) {
                        formattedValue = '';
                    } else {
                        formattedValue = String(value);
                    }

                    formData.append(key, formattedValue);
                }
            });

            router.post(route('admin.account.closed_period.store'), formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page: any) => {
                    this.isLoading = false;
                    if (page.props.flash?.success) {
                        toast.success(page.props.flash.success);
                    }
                },
                onError: (errors) => {
                    this.isLoading = false;
                    if (errors && Object.keys(errors).length > 0) {
                        const firstErrorArray = Object.values(errors)[0];
                        const firstErrorMessage = Array.isArray(firstErrorArray) ? firstErrorArray[0] : firstErrorArray;
                        toast.error(firstErrorMessage);
                    } else {
                        toast.error(`Gagal menambahkan penerimaan barang ${closed_period.no_closed}`);
                    }
                },
            });
        },

        approvalClosedPeriod(slug: string, closed_period: Partial<ClosedPeriodApproval>) {
            this.isLoading = true;

            const formatDateWithCurrentTime = (dateString: string | undefined | null) => {
                if (!dateString) return null;
                const inputDate = new Date(dateString);
                const now = new Date();
                inputDate.setHours(now.getHours());
                inputDate.setMinutes(now.getMinutes());
                inputDate.setSeconds(now.getSeconds());
                return inputDate.toISOString().slice(0, 19).replace('T', ' ');
            };

            const formDataApproval = new FormData();

            const payload = {
                ...closed_period,
                ack_date: formatDateWithCurrentTime(closed_period.ack_date),
                reject_date: formatDateWithCurrentTime(closed_period.reject_date),
            };

            Object.entries(payload).forEach(([key, value]) => {
                if (value !== undefined) {
                    let formattedValue = value;

                    if (typeof value === 'boolean') {
                        formattedValue = value ? '1' : '0';
                    } else if (value === null || value === '' || value === undefined) {
                        formattedValue = '';
                    } else {
                        formattedValue = String(value);
                    }

                    formDataApproval.append(key, formattedValue);
                }
            });

            formDataApproval.append('_method', 'PATCH');

            router.post(route('admin.account.closed_period.approval.submit', { closed_period: slug }), formDataApproval, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page: any) => {
                    this.isLoading = false;
                    if (page.props.flash?.success) {
                        toast.success(page.props.flash.success);
                    }
                },
                onError: (errors) => {
                    this.isLoading = false;
                    if (errors && Object.keys(errors).length > 0) {
                        const firstErrorArray = Object.values(errors)[0];
                        const firstErrorMessage = Array.isArray(firstErrorArray) ? firstErrorArray[0] : firstErrorArray;
                        toast.error(firstErrorMessage);
                    } else {
                        toast.error(`Gagal approval setting periode`);
                    }
                },
            });
        },

        closedPeriod(slug: string, closed_period: Partial<ClosedPeriodApproval>) {
            this.isLoading = true;

            const formDataClosed = new FormData();

            const payload = {
                ...closed_period
            };

            Object.entries(payload).forEach(([key, value]) => {
                if (value !== undefined) {
                    let formattedValue = value;

                    if (typeof value === 'boolean') {
                        formattedValue = value ? '1' : '0';
                    } else if (value === null || value === '' || value === undefined) {
                        formattedValue = '';
                    } else {
                        formattedValue = String(value);
                    }

                    formDataClosed.append(key, formattedValue);
                }
            });

            formDataClosed.append('_method', 'PATCH');

            router.post(route('admin.account.closed_period.closed.submit', { closed_period: slug }), formDataClosed, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                preserveState: true,
                preserveScroll: true,
                onSuccess: (page: any) => {
                    this.isLoading = false;
                    if (page.props.flash?.success) {
                        toast.success(page.props.flash.success);
                    }
                },
                onError: (errors) => {
                    this.isLoading = false;
                    if (errors && Object.keys(errors).length > 0) {
                        const firstErrorArray = Object.values(errors)[0];
                        const firstErrorMessage = Array.isArray(firstErrorArray) ? firstErrorArray[0] : firstErrorArray;
                        toast.error(firstErrorMessage);
                    } else {
                        toast.error(`Gagal approval setting periode`);
                    }
                },
            });
        },
    },
});
