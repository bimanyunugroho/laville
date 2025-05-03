import { GoodReceipt, GoodReceiptApproval, GoodReceiptPageProps, GoodReceiptPagination, PaginationLink } from '@/types/good_receipt';
import { PurchaseOrder } from '@/types/purchase_order';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { debounce, isEmpty } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface GoodReceiptState {
    good_receipts: GoodReceiptPagination;
    searchQuery: string;
    isLoading: boolean;
    purchaseOrderQuery: string;
    purchaseOrderResult: PurchaseOrder | null;
    purchaseOrderSearchLoading: boolean;
}

const toast = useToast();

export const useGoodReceiptStore = defineStore('good_receipt', {
    state: (): GoodReceiptState => ({
        good_receipts: {
            data: [] as GoodReceipt[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
        purchaseOrderQuery: '',
        purchaseOrderResult: null,
        purchaseOrderSearchLoading: false,
    }),

    actions: {
        initializeFromProps(props: GoodReceiptPageProps) {
            this.good_receipts = props.good_receipts;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: GoodReceiptState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.good_receipt.index'),
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

        setPurchaseOrderQuery(query: string) {
            this.purchaseOrderQuery = query;
            if (query) {
                this.debouncePOSearch(query);
            } else {
                this.purchaseOrderResult = null;
            }
        },

        debouncePOSearch: debounce(function (this: GoodReceiptState & { debouncePOSearch: Function }, query: string) {
            if (!query) return;

            (this as any).searchNoPurchaseOrder();
        }, 300),

        searchNoPurchaseOrder() {
            if (!this.purchaseOrderQuery) {
                this.purchaseOrderResult = null;
                return;
            }

            this.purchaseOrderSearchLoading = true;

            axios
                .get(route('admin.inventory.search_by_no_po'), {
                    params: { po_number: this.purchaseOrderQuery },
                })
                .then((response) => {
                    const data = response.data.data;

                    if (!data || data.length === 0) {
                        toast.warning(response.data.message || 'Purchase Order tidak ditemukan');
                        this.purchaseOrderResult = null;
                    } else {
                        this.purchaseOrderResult = data[0];
                    }
                })
                .catch((error) => {
                    toast.error(error);
                    this.purchaseOrderResult = null;
                })
                .finally(() => {
                    this.purchaseOrderSearchLoading = false;
                });
        },

        clearPurchaseOrderSearch() {
            this.purchaseOrderResult = null;
            this.purchaseOrderQuery = '';
        },

        createGoodReceipt(good_receipt: Partial<GoodReceipt>) {
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
                ...good_receipt,
                purchase_order_id: good_receipt.purchase_order_id ?? good_receipt.purchaseOrder?.id,
                supplier_id: good_receipt.supplier_id ?? good_receipt.supplier?.id,
                user_id: good_receipt.user_id ?? good_receipt.user?.id,
                user_ack_id: good_receipt.user_ack_id ?? null,
                po_date: formatDateWithCurrentTime(good_receipt.receipt_date),
                receipt_date: formatDateWithCurrentTime(good_receipt.receipt_date)
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
            if (good_receipt.details && Array.isArray(good_receipt.details)) {
                good_receipt.details.forEach((detail, index) => {
                    formData.append(`details[${index}][purchase_order_detail_id]`, String(detail.purchase_order_detail_id));
                    formData.append(`details[${index}][product_id]`, String(detail.product_id));
                    formData.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formData.append(`details[${index}][quantity]`, String(detail.quantity));
                    formData.append(`details[${index}][base_quantity]`, String(detail.base_quantity));
                    formData.append(`details[${index}][price]`, String(detail.price));
                    formData.append(`details[${index}][subtotal]`, String(detail.subtotal));

                    if (detail.received_quantity !== undefined && detail.received_quantity !== null) {
                        formData.append(`details[${index}][received_quantity]`, String(detail.received_quantity));
                    }
                    if (detail.received_base_quantity !== undefined && detail.received_base_quantity !== null) {
                        formData.append(`details[${index}][received_base_quantity]`, String(detail.received_base_quantity));
                    }
                });
            }

            router.post(route('admin.inventory.good_receipt.store'), formData, {
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
                        toast.error(`Gagal menambahkan penerimaan barang ${good_receipt.receipt_number}`);
                    }
                },
            });
        },

        editGoodReceipt(slug: string, good_receipt: Partial<GoodReceipt>) {
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
                ...good_receipt,
                purchase_order_id: good_receipt.purchase_order_id ?? good_receipt.purchaseOrder?.id,
                supplier_id: good_receipt.supplier_id ?? good_receipt.supplier?.id,
                user_id: good_receipt.user_id ?? good_receipt.user?.id,
                user_ack_id: good_receipt.user_ack_id ?? null,
                po_date: formatDateWithCurrentTime(good_receipt.receipt_date),
                receipt_date: formatDateWithCurrentTime(good_receipt.receipt_date)
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

                    formDataEdit.append(key, formattedValue);
                }
            });

            // Tangani details array
            if (good_receipt.details && Array.isArray(good_receipt.details)) {
                good_receipt.details.forEach((detail, index) => {
                    formDataEdit.append(`details[${index}][purchase_order_detail_id]`, String(detail.purchase_order_detail_id));
                    formDataEdit.append(`details[${index}][product_id]`, String(detail.product_id));
                    formDataEdit.append(`details[${index}][unit_id]`, String(detail.unit_id));
                    formDataEdit.append(`details[${index}][quantity]`, String(detail.quantity));
                    formDataEdit.append(`details[${index}][base_quantity]`, String(detail.base_quantity));
                    formDataEdit.append(`details[${index}][price]`, String(detail.price));
                    formDataEdit.append(`details[${index}][subtotal]`, String(detail.subtotal));

                    if (detail.received_quantity !== undefined && detail.received_quantity !== null) {
                        formDataEdit.append(`details[${index}][received_quantity]`, String(detail.received_quantity));
                    }
                    if (detail.received_base_quantity !== undefined && detail.received_base_quantity !== null) {
                        formDataEdit.append(`details[${index}][received_base_quantity]`, String(detail.received_base_quantity));
                    }
                });
            }

            formDataEdit.append('_method', 'PUT');
            router.post(route('admin.inventory.good_receipt.update', { good_receipt: slug }), formDataEdit, {
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
                        toast.error(`Gagal menambahkan penerimaan barang ${good_receipt.receipt_number}`);
                    }
                },
            });
        },

        async deleteGoodReceipt(good_receipt: GoodReceipt) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus Penerimaan Barang <strong>${good_receipt.receipt_number}</strong>?`,
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
                    router.delete(route('admin.inventory.good_receipt.destroy', good_receipt.slug), {
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
                                toast.error(`Gagal menghapus data penerimaan barang ${good_receipt.receipt_number}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },

        approvalGoodReceipt(slug: string, good_receipt: Partial<GoodReceiptApproval>) {
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
                ...good_receipt,
                ack_date: formatDateWithCurrentTime(good_receipt.ack_date),
                reject_date: formatDateWithCurrentTime(good_receipt.reject_date),
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
            router.post(route('admin.inventory.good_receipt.approval.submit', { good_receipt: slug }), formDataApproval, {
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
                        toast.error(`Gagal approval purchase order`);
                    }
                },
            });
        },
    },
});
