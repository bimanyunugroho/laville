import { PaginationLink, Product, ProductPageProps, ProductPagination } from '@/types/produk';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface ProductState {
    products: ProductPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useProductStore = defineStore('product', {
    state: (): ProductState => ({
        products: {
            data: [] as Product[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: ProductPageProps) {
            this.products = props.products;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: ProductState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.master.product.index'),
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

        createProduct(product: Partial<Product>) {
            this.isLoading = true;

            const payload =  {
                ...product,
                default_unit_id: product.default_unit_id,
                defaultUnit: undefined
            };

            router.post(route('admin.master.product.store'), payload, {
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
                        toast.error(`Gagal menambahkan satuan ${product.name}`);
                    }
                },
            });
        },

        editProduct(slug: string, product: Partial<Product>) {
            this.isLoading = true;

            const payload = {
                ...product,
                default_unit_id: product.defaultUnit?.id || product.default_unit_id,
                defaultUnit: undefined
            };

            router.put(route('admin.master.product.update', { slug }), payload, {
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
                        toast.error(`Gagal menambahkan satuan ${product.name}`);
                    }
                },
            });
        },

        async deleteProduct(product: Product) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus produk <strong>${product.name}</strong>?`,
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
                    router.delete(route('admin.master.product.destroy', product.slug), {
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
                                toast.error(`Gagal menghapus satuan ${product.name}`);
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
