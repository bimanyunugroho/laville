import { PaginationLink, UnitConversion, UnitConversionPageProps, UnitConversionPagination } from "@/types/unit_conversion";
import { debounce } from "lodash";
import { defineStore } from "pinia";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";
import { router } from "@inertiajs/vue3";


interface UnitConversionState {
    unit_conversions: UnitConversionPagination;
    searchQuery: string;
    isLoading: boolean;
}

const toast = useToast();

export const useUnitConversionStore = defineStore('unit_conversion', {
    state: (): UnitConversionState => ({
        unit_conversions: {
            data: [] as UnitConversion[],
            links: [] as PaginationLink[],
            current_page: 1,
            per_page: 10,
            total: 0,
        },
        searchQuery: '',
        isLoading: false,
    }),

    actions: {
        initializeFromProps(props: UnitConversionPageProps) {
            this.unit_conversions = props.unit_conversions;
        },

        setSearchQuery(query: string) {
            this.searchQuery = query;
            this.debounceSearch(query);
        },

        debounceSearch: debounce(function (this: UnitConversionState & { debounceSearch: Function }, query: string) {
            this.isLoading = true;
            router.get(
                route('admin.master.unit_conversion.index'),
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

        createUnitConversion(unit_conversion: Partial<UnitConversion>) {
            this.isLoading = true;

            const payload =  {
                ...unit_conversion,
                product_id: unit_conversion.product_id,
                from_unit_id: unit_conversion.from_unit_id,
                to_unit_id: unit_conversion.to_unit_id,
                product: undefined,
                fromUnit: undefined,
                toUnit: undefined
            };

            router.post(route('admin.master.unit_conversion.store'), payload, {
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
                        toast.error(`Gagal menambahkan satuan ${unit_conversion.product?.name}`);
                    }
                },
            });
        },

        editUnitConversion(slug: string, unit_conversion: Partial<UnitConversion>) {
            this.isLoading = true;

            const payload =  {
                ...unit_conversion,
                product_id: unit_conversion.product?.id || unit_conversion.product_id,
                from_unit_id: unit_conversion.fromUnit?.id || unit_conversion.from_unit_id,
                to_unit_id: unit_conversion.toUnit?.id || unit_conversion.to_unit_id,
                product: undefined,
                fromUnit: undefined,
                toUnit: undefined
            };

            router.put(route('admin.master.unit_conversion.update', { unit_conversion: slug }), payload, {
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
                        toast.error(`Gagal menambahkan satuan ${unit_conversion.product?.name}`);
                    }
                },
            });
        },

        async deleteUnitConversion(unit_conversion: UnitConversion) {
            const result = await Swal.fire({
                title: 'Perhatian',
                html: `Apakah Anda yakin ingin menghapus konversi produk <strong>${unit_conversion.product?.name}</strong>?`,
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
                    router.delete(route('admin.master.unit_conversion.destroy', unit_conversion.slug), {
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
                                toast.error(`Gagal menghapus satuan ${unit_conversion.product?.name}`);
                            }
                        },
                    });
                } catch (error) {
                    this.isLoading = false;
                    toast.error(`Terjadi kesalahan: ${error}`);
                }
            }
        },


    }
});
