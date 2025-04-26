import { Customer, CustomerPageProps, CustomerPagination, PaginationLink } from "@/types/customer";
import { debounce } from "lodash";
import { defineStore } from "pinia";
import { useToast } from "vue-toastification";
import { router } from "@inertiajs/vue3";
import Swal from "sweetalert2";

interface CustomerState {
    customers: CustomerPagination
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useCustomerStore = defineStore('customer', {
    state: (): CustomerState => ({
        customers: {
            data: [] as Customer[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
            initializeFromProps(props: CustomerPageProps) {
                this.customers = props.customers;
            },

            setSearchQuery(query: string) {
                this.searchQuery = query;
                this.debouncedSearch(query);
            },

            debouncedSearch: debounce(function (this: CustomerState & { debouncedSearch: Function }, query: string) {
                this.isLoading = true;
                router.get(
                    route('admin.transaksi.customer.index'),
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

            createCustomer(customer: Partial<Customer>) {
                this.isLoading = true;

                router.post(route('admin.transaksi.customer.store'), customer, {
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
                            toast.error(`Gagal menambahkan satuan ${customer.name}`);
                        }
                    },
                });
            },

            editCustomer(slug: string, customer: Partial<Customer>) {
                this.isLoading = true;

                router.put(route('admin.transaksi.customer.update', { slug }), customer, {
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
                            toast.error(`Gagal mengubah satuan ${customer.name}`);
                        }
                    },
                });
            },

            async deleteCustomer(customer: Customer) {
                const result = await Swal.fire({
                    title: 'Perhatian',
                    html: `Apakah Anda yakin ingin menghapus pelanggan <strong>${customer.name}</strong>?`,
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
                        router.delete(route('admin.transaksi.customer.destroy', customer.slug), {
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
                                    toast.error(`Gagal menghapus satuan ${customer.name}`);
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
