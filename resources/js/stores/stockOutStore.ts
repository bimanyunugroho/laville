import { PaginationLink, StockOut, StockOutApproval, StockOutPageProps, StockOutPagination } from '@/types/stock_out';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface StockOutState {
    stock_outs: StockOutPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useStockOutStore = defineStore('stock_out', {
    state: (): StockOutState => ({
        stock_outs: {
            data: [] as StockOut[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: StockOutPageProps) {
            this.stock_outs = props.stock_outs;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: StockOutState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.stock_out.index'),
                { search: query },
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

        goToPage(url: string) {
            if (!url) return;

            this.isLoading = true;
            router.visit(url, {
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

        createStockOut(stock_out: Partial<StockOut>) {
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
                ...stock_out,
                supplier_id: stock_out.supplier_id ?? stock_out.supplier?.id,
                user_id: stock_out.user_id ?? stock_out.user?.id,
                user_ack_id: stock_out.user_ack_id ?? null,
                out_date: formatDateWithCurrentTime(stock_out.out_date),
            };

            delete payload.supplier;
            delete payload.user;
            delete payload.userAck;
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
            if (stock_out.details && Array.isArray(stock_out.details)) {
                stock_out.details.forEach((detail, index) => {
                    formData.append(`details[${index}][product_id]`, String(detail.product_id));
                    formData.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formData.append(`details[${index}][quantity]`, String(detail.quantity));
                    formData.append(`details[${index}][base_quantity]`, String(detail.base_quantity));
                    formData.append(`details[${index}][price]`, String(detail.price));
                    formData.append(`details[${index}][subtotal]`, String(detail.subtotal));
                    formData.append(`details[${index}][notes_detail]`, String(detail.notes_detail));

                    if (detail.received_quantity !== undefined && detail.received_quantity !== null) {
                        formData.append(`details[${index}][received_quantity]`, String(detail.received_quantity));
                    }
                    if (detail.received_base_quantity !== undefined && detail.received_base_quantity !== null) {
                        formData.append(`details[${index}][received_base_quantity]`, String(detail.received_base_quantity));
                    }
                });
            }

            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            router.post(route('admin.inventory.stock_out.store'), formData, {
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
                        toast.error(`Gagal menambahkan pengeluaran barang ${stock_out.stock_out_number}`);
                    }
                },
            });
        },

        editStockOut(slug: string, stock_out: Partial<StockOut>) {
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
                ...stock_out,
                supplier_id: stock_out.supplier_id ?? stock_out.supplier?.id,
                user_id: stock_out.user_id ?? stock_out.user?.id,
                user_ack_id: stock_out.user_ack_id ?? null,
                out_date: formatDateWithCurrentTime(stock_out.out_date),
            };

            delete payload.user;
            delete payload.userAck;
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
            if (stock_out.details && Array.isArray(stock_out.details)) {
                stock_out.details.forEach((detail, index) => {
                    formDataEdit.append(`details[${index}][product_id]`, String(detail.product_id));
                    formDataEdit.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formDataEdit.append(`details[${index}][quantity]`, String(detail.quantity));
                    formDataEdit.append(`details[${index}][base_quantity]`, String(detail.base_quantity));
                    formDataEdit.append(`details[${index}][price]`, String(detail.price));
                    formDataEdit.append(`details[${index}][subtotal]`, String(detail.subtotal));
                    formDataEdit.append(`details[${index}][notes_detail]`, String(detail.notes_detail));

                    if (detail.received_quantity !== undefined && detail.received_quantity !== null) {
                        formDataEdit.append(`details[${index}][received_quantity]`, String(detail.received_quantity));
                    }
                    if (detail.received_base_quantity !== undefined && detail.received_base_quantity !== null) {
                        formDataEdit.append(`details[${index}][received_base_quantity]`, String(detail.received_base_quantity));
                    }
                });
            }

            for (const [key, value] of formDataEdit.entries()) {
                console.log(`${key}: ${value}`);
            }

            formDataEdit.append('_method', 'PUT');
            router.post(route('admin.inventory.stock_out.update', { stock_out: slug }), formDataEdit, {
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
                        toast.error(`Gagal menambahkan data pengeluaran barang ${stock_out.stock_out_number}`);
                    }
                },
            });
        },

        async deleteStockOut(stock_out: StockOut) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus Pengeluaran Barang <strong>${stock_out.stock_out_number}</strong>?`,
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
                    router.delete(route('admin.inventory.stock_out.destroy', stock_out.slug), {
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
                                toast.error(`Gagal menghapus data pengeluaran barang ${stock_out.stock_out_number}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },

        approvalStockOut(slug: string, stock_out: Partial<StockOutApproval>) {
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
                ...stock_out,
                ack_date: formatDateWithCurrentTime(stock_out.ack_date),
                reject_date: formatDateWithCurrentTime(stock_out.reject_date),
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

                    formDataEdit.append(key, formattedValue);
                }
            });

            formDataEdit.append('_method', 'PATCH');
            router.post(route('admin.inventory.stock_out.approval.submit', { stock_out: slug }), formDataEdit, {
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
                        toast.error(`Gagal approval pengeluaran barang`);
                    }
                },
            });
        },
    },
});
