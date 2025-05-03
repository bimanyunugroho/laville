import { PaginationLink, PurchaseOrder, PurchaseOrderApproval, PurchaseOrderPageProps, PurchaseOrderPagination } from '@/types/purchase_order';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface PurchaseOrderState {
    purchase_orders: PurchaseOrderPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const usePurchaseOrderStore = defineStore('purchase_order', {
    state: (): PurchaseOrderState => ({
        purchase_orders: {
            data: [] as PurchaseOrder[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: PurchaseOrderPageProps) {
            this.purchase_orders = props.purchase_orders;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: PurchaseOrderState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.purchase_order.index'),
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

        createPurchaseOrder(purchase_order: Partial<PurchaseOrder>) {
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
                ...purchase_order,
                supplier_id: purchase_order.supplier_id ?? purchase_order.supplier?.id,
                user_id: purchase_order.user_id ?? purchase_order.user?.id,
                user_ack_id: purchase_order.user_ack_id ?? null,
                po_date: formatDateWithCurrentTime(purchase_order.po_date),
                expected_date: formatDateWithCurrentTime(purchase_order.expected_date)
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
            if (purchase_order.details && Array.isArray(purchase_order.details)) {
                purchase_order.details.forEach((detail, index) => {
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

            router.post(route('admin.inventory.purchase_order.store'), formData, {
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
                        toast.error(`Gagal menambahkan satuan ${purchase_order.po_number}`);
                    }
                },
            });
        },

        editPurchaseOrder(slug: string, purchase_order: Partial<PurchaseOrder>) {
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
                ...purchase_order,
                supplier_id: purchase_order.supplier_id ?? purchase_order.supplier?.id,
                user_id: purchase_order.user_id ?? purchase_order.user?.id,
                user_ack_id: purchase_order.user_ack_id ?? null,
                po_date: formatDateWithCurrentTime(purchase_order.po_date),
                expected_date: formatDateWithCurrentTime(purchase_order.expected_date)
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
            if (purchase_order.details && Array.isArray(purchase_order.details)) {
                purchase_order.details.forEach((detail, index) => {
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
            router.post(route('admin.inventory.purchase_order.update', { purchase_order: slug }), formDataEdit, {
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
                        toast.error(`Gagal menambahkan data purchase order ${purchase_order.po_number}`);
                    }
                },
            });
        },

        async deletePurchaseOrder(purchase_order: PurchaseOrder) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus PO <strong>${purchase_order.po_number}</strong>?`,
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
                    router.delete(route('admin.inventory.purchase_order.destroy', purchase_order.slug), {
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
                                toast.error(`Gagal menghapus data purchase order ${purchase_order.po_number}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },

        approvalPurchaseOrder(slug: string, purchase_order: Partial<PurchaseOrderApproval>) {
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
                ...purchase_order,
                ack_date: formatDateWithCurrentTime(purchase_order.ack_date),
                reject_date: formatDateWithCurrentTime(purchase_order.reject_date),
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
            router.post(route('admin.inventory.purchase_order.approval.submit', { purchase_order: slug }), formDataEdit, {
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
