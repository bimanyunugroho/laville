import { PaginationLink, Supplier, SupplierPageProps, SupplierPagination } from '@/types/supplier';
import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

interface SupplierState {
    suppliers: SupplierPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useSupplierStore = defineStore('supplier', {
    state: (): SupplierState => ({
        suppliers: {
            data: [] as Supplier[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: SupplierPageProps) {
            this.suppliers = props.suppliers;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: SupplierState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.inventory.supplier.index'),
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

        createSupplier(supplier: Partial<Supplier>) {
            this.isLoading = true;

            router.post(route('admin.inventory.supplier.store'), supplier, {
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
                        toast.error(`Gagal menambahkan satuan ${supplier.name}`);
                    }
                },
            });
        },

        editSupplier(slug: string, supplier: Partial<Supplier>) {
            this.isLoading = true;

            router.put(route('admin.inventory.supplier.update', { slug }), supplier, {
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
                        toast.error(`Gagal menambahkan satuan ${supplier.name}`);
                    }
                },
            });
        },

        async deleteSupplier(supplier: Supplier) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus supplier <strong>${supplier.name}</strong>?`,
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
                    router.delete(route('admin.inventory.supplier.destroy', supplier.slug), {
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
                                toast.error(`Gagal menghapus satuan ${supplier.name}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },
    },
});
