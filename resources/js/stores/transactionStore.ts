import { PaginationLink, Transaction, TransactionPageProps, TransactionPagination } from '@/types/transaction';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface TransactionState {
    transactions: TransactionPagination;
    searchQuery: string;
    dateFromFilter: string;
    dateToFilter: string;
    statusTransactionFilter: string;
    isLoading: boolean;
}

const toast = useToast();

export const useTransactionStore = defineStore('transaction', {
    state: (): TransactionState => ({
        transactions: {
            data: [] as Transaction[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        dateFromFilter: '',
        dateToFilter: '',
        statusTransactionFilter: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: TransactionPageProps) {
            this.transactions = props.transactions;

            const urlParams = new URL(window.location.href).searchParams;
            this.searchQuery = urlParams.get('search') || '';
            this.dateFromFilter = urlParams.get('date_from') || '';
            this.dateToFilter = urlParams.get('date_to') || '';
            this.statusTransactionFilter = urlParams.get('status') || '';
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debouncedSearch();
        },

        setDateFromFilter(query: string) {
            this.dateFromFilter = query;
            this.debouncedSearch();
        },

        setDateToFilter(query: string) {
            this.dateToFilter = query;
            this.debouncedSearch();
        },

        setStatusTransactionFilter(query: string) {
            this.statusTransactionFilter = query;
            this.debouncedSearch();
        },

        debouncedSearch: debounce(function (this: any) {
            this.isLoading = true;
            router.get(
                route(''),
                {
                    search: this.searchQuery,
                    date_from: this.dateFromFilter,
                    date_to: this.dateToFilter,
                    status: this.setStatusTransactionFilter,
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
            this.dateFromFilter = '';
            this.dateToFilter = '';
            this.statusTransactionFilter = '';
            this.debouncedSearch();
        },

        goToPage(url: string) {
            if (!url) return;

            this.isLoading = true;

            const urlObj = new URL(url);
            if (this.searchQuery) urlObj.searchParams.set('search', this.searchQuery);
            if (this.dateFromFilter) urlObj.searchParams.set('date_from', this.dateFromFilter);
            if (this.dateToFilter) urlObj.searchParams.set('date_to', this.dateToFilter);
            if (this.statusTransactionFilter) urlObj.searchParams.set('status', this.statusTransactionFilter);

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

        createTransaction(transaction: Partial<Transaction>) {
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

            const fromDataCreate = new FormData();

            const payload = {
                ...transaction,
                user_id: transaction.user_id ?? transaction.user?.id,
                transaction_date: formatDateWithCurrentTime(transaction.transaction_date),
            };

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

                    fromDataCreate.append(key, formattedValue);
                }
            });

            if (transaction.details && Array.isArray(transaction.details)) {
                transaction.details.forEach((detail, index) => {
                    fromDataCreate.append(`details[${index}][product_id]`, String(detail.product_id));
                    fromDataCreate.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    fromDataCreate.append(`details[${index}][quantity]`, String(detail.quantity));
                    fromDataCreate.append(`details[${index}][base_quantity]`, String(detail.base_quantity));
                    fromDataCreate.append(`details[${index}][price]`, String(detail.price));
                    fromDataCreate.append(`details[${index}][discount]`, String(detail.discount));
                    fromDataCreate.append(`details[${index}][subtotal]`, String(detail.subtotal));
                });
            }

            if (transaction.payments && Array.isArray(transaction.payments)) {
                transaction.payments.forEach((payment, index) => {
                    fromDataCreate.append(`payments[${index}][payment_date]`, String(payment.payment_date));
                    fromDataCreate.append(`payments[${index}][payment_method]`, String(payment.payment_method));
                    fromDataCreate.append(`payments[${index}][payment_reference]`, String(payment.payment_reference));
                    fromDataCreate.append(`payments[${index}][amount]`, String(payment.amount));
                    fromDataCreate.append(`payments[${index}][status]`, String(payment.status));
                });
            }

            router.post(route('admin.transaksi.transaction.store'), fromDataCreate, {
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
                        toast.error(`Gagal ${transaction.invoice_number}`);
                    }
                },
            });
        }
    },
});
