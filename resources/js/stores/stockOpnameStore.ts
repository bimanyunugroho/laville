import { PaginationLink, StockOpname, StockOpnameApproval, StockOpnamePageProps, StockOpnamePagination, StockOpnameValidate } from "@/types/stock_opname";
import { defineStore } from "pinia";
import { router } from "@inertiajs/vue3";
import { debounce } from 'lodash';
import { useToast } from "vue-toastification";
import Swal from "sweetalert2";

interface StockOpnameState {
    stock_opnames: StockOpnamePagination;
    searchQuery: string;
    monthFilter: string;
    yearFilter: string;
    isLoading: boolean;
}

const toast = useToast();

export const useStockOpnameStore = defineStore('stock_opname', {
    state: (): StockOpnameState => ({
        stock_opnames: {
            data: [] as StockOpname[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0
        },
        searchQuery: '',
        monthFilter: '',
        yearFilter: '',
        isLoading: false
    }),

    actions: {
        initializeFromProps(props: StockOpnamePageProps) {
            this.stock_opnames = props.stock_opnames;

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

        debouncedSearch: debounce(function(this: any) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.stock_opname.index'),
                {
                    search: this.searchQuery,
                    month: this.monthFilter,
                    year: this.yearFilter
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

        createStockOpname(stock_opname: Partial<StockOpname>) {
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
                ...stock_opname,
                user_id: stock_opname.user_id ?? stock_opname.user?.id,
                user_validator_id: stock_opname.user_validator_id ?? null,
                user_ack_id: stock_opname.user_ack_id ?? null,
                user_reject_id: stock_opname.user_reject_id ?? null,
                so_date: formatDateWithCurrentTime(stock_opname.so_date)
            };

            delete payload.user;
            delete payload.userValidator;
            delete payload.userAck;
            delete payload.userReject;
            delete payload.details;

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

            // Tangani details array
            if (stock_opname.details && Array.isArray(stock_opname.details)) {
                stock_opname.details.forEach((detail, index) => {
                    formData.append(`details[${index}][product_id]`, String(detail.product_id));
                    formData.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formData.append(`details[${index}][system_stock]`, String(detail.system_stock));
                    formData.append(`details[${index}][system_stock_base]`, String(detail.system_stock_base));
                    formData.append(`details[${index}][physical_stock]`, String(detail.physical_stock));
                    formData.append(`details[${index}][physical_stock_base]`, String(detail.physical_stock_base));
                    formData.append(`details[${index}][difference_stock]`, String(detail.difference_stock));
                    formData.append(`details[${index}][difference_stock_base]`, String(detail.difference_stock_base));
                    formData.append(`details[${index}][price]`, String(detail.price));
                    formData.append(`details[${index}][subtotal]`, String(detail.subtotal));
                    formData.append(`details[${index}][status]`, String(detail.status));
                    formData.append(`details[${index}][notes]`, String(detail.notes));
                });
            }

            router.post(route('admin.inventory.stock_opname.store'), formData, {
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
                        toast.error(`Gagal menambahkan satuan ${stock_opname.so_number}`);
                    }
                },
            });
        },

        editStockOpname(slug: string, stock_opname: Partial<StockOpname>) {
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

            const formDataEdit = new FormData();

            const payload = {
                ...stock_opname,
                user_id: stock_opname.user_id ?? stock_opname.user?.id,
                user_validator_id: stock_opname.user_validator_id ?? null,
                user_ack_id: stock_opname.user_ack_id ?? null,
                user_reject_id: stock_opname.user_reject_id ?? null,
                so_date: formatDateWithCurrentTime(stock_opname.so_date)
            };

            delete payload.user;
            delete payload.userValidator;
            delete payload.userAck;
            delete payload.userReject;
            delete payload.details;

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

                    formDataEdit.append(key, formattedValue);
                }
            });

            // Tangani details array
            if (stock_opname.details && Array.isArray(stock_opname.details)) {
                stock_opname.details.forEach((detail, index) => {
                    formDataEdit.append(`details[${index}][product_id]`, String(detail.product_id));
                    formDataEdit.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formDataEdit.append(`details[${index}][system_stock]`, String(detail.system_stock));
                    formDataEdit.append(`details[${index}][system_stock_base]`, String(detail.system_stock_base));
                    formDataEdit.append(`details[${index}][physical_stock]`, String(detail.physical_stock));
                    formDataEdit.append(`details[${index}][physical_stock_base]`, String(detail.physical_stock_base));
                    formDataEdit.append(`details[${index}][difference_stock]`, String(detail.difference_stock));
                    formDataEdit.append(`details[${index}][difference_stock_base]`, String(detail.difference_stock_base));
                    formDataEdit.append(`details[${index}][price]`, String(detail.price));
                    formDataEdit.append(`details[${index}][subtotal]`, String(detail.subtotal));
                    formDataEdit.append(`details[${index}][status]`, String(detail.status));
                    formDataEdit.append(`details[${index}][notes]`, String(detail.notes));
                });
            }

            formDataEdit.append('_method', 'PUT');

            router.post(route('admin.inventory.stock_opname.update', { stock_opname: slug }), formDataEdit, {
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
                        toast.error(`Gagal menambahkan data stock opname ${stock_opname.so_number}`);
                    }
                },
            });
        },

        async deleteStockOpname(stock_opname: StockOpname) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus SO <strong>${stock_opname.so_number}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            });

            if (result.isConfirmed) {
                try {
                    this.isLoading = true;
                    router.delete(route('admin.inventory.stock_opname.destroy', stock_opname.slug), {
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
                                toast.error(`Gagal menghapus data purchase order ${stock_opname.so_number}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },

        validateStockOpname(slug: string, stock_opname: Partial<StockOpnameValidate>) {
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

            const formDataValidate = new FormData();

            const payload = {
                ...stock_opname,
                validator_date: formatDateWithCurrentTime(stock_opname.validator_date),
                ack_date: formatDateWithCurrentTime(stock_opname.ack_date),
                reject_date: formatDateWithCurrentTime(stock_opname.reject_date),
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

                    formDataValidate.append(key, formattedValue);
                }
            });

            formDataValidate.append('_method', 'PATCH');

            router.post(route('admin.inventory.stock_opname.validator.submit', { stock_opname: slug }), formDataValidate, {
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
                        toast.error(`Gagal validate stock opname`);
                    }
                },
            });
        },

        approvalStockOpname(slug: string, stock_opname: Partial<StockOpnameApproval>) {
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
                ...stock_opname,
                validator_date: formatDateWithCurrentTime(stock_opname.validator_date),
                ack_date: formatDateWithCurrentTime(stock_opname.ack_date),
                reject_date: formatDateWithCurrentTime(stock_opname.reject_date),
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
            router.post(route('admin.inventory.stock_opname.approval.submit', { stock_opname: slug }), formDataApproval, {
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
                        toast.error(`Gagal approval stock opname`);
                    }
                },
            });
        },

    }


});
