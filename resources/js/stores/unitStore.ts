import { PaginationLink, Unit, UnitPageProps, UnitPagination } from '@/types/unit';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import { defineStore } from 'pinia';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

interface UnitState {
    units: UnitPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useUnitStore = defineStore('unit', {
    state: (): UnitState => ({
        units: {
            data: [] as Unit[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: UnitPageProps) {
            this.units = props.units;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debouncedSearch(query);
        },

        debouncedSearch: debounce(function (this: UnitState & { debouncedSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.master.unit.index'),
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

        createUnit(unit: Partial<Unit>) {
            this.isLoading = true;

            router.post(route('admin.master.unit.store'), unit, {
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
                        toast.error(`Gagal menambahkan satuan ${unit.name}`);
                    }
                },
            });
        },

        editUnit(slug: string, unit: Partial<Unit>) {
            this.isLoading = true;

            router.put(route('admin.master.unit.update', { slug }), unit, {
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
                        toast.error(`Gagal mengubah satuan ${unit.name}`);
                    }
                },
            });
        },

        async deleteUnit(unit: Unit) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus satuan <strong>${unit.name}</strong>?`,
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
                    router.delete(route('admin.master.unit.destroy', unit.slug), {
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
                                toast.error(`Gagal menghapus satuan ${unit.name}`);
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
