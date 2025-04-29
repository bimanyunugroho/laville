<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import CheckboxInput from '@/components/CheckboxInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import TextNumber from '@/components/TextNumber.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import VSelect from '@/components/VSelect.vue';
import { Unit } from '@/types/unit';
import { Product } from '@/types/produk';
import { useUnitConversionStore } from '@/stores/unitConversionStore';
import Swal from 'sweetalert2';
import { UnitConversion } from '@/types/unit_conversion';

const props = defineProps<{
    title: string;
    desc: string;
    unit_conversion: UnitConversion;
    products: Product[];
    from_units: Unit[];
    to_units: Unit[];
}>();

const unitConversionStore = useUnitConversionStore();

const newUnitConversion = ref({
    product_id: props.unit_conversion.product_id,
    from_unit_id: props.unit_conversion.from_unit_id,
    to_unit_id: props.unit_conversion.to_unit_id,
    conversion_factor: props.unit_conversion.conversion_factor
});

watch(() => newUnitConversion.value.product_id, (newProductId) => {
    if (newProductId) {
        const selectedProduct = props.products.find(product => product.id === newProductId);

        if (selectedProduct) {
            if (selectedProduct.defaultUnit?.id) {
                newUnitConversion.value.from_unit_id = selectedProduct.defaultUnit?.id
            } else {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Produk ini tidak memiliki satuan default yang ditetapkan.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                newUnitConversion.value.from_unit_id = 0;
            }
        }
    }
});

const selectedToUnitLabel = computed(() => {
    const selectedUnit = props.to_units.find(unit => unit.id === newUnitConversion.value.to_unit_id);
    return selectedUnit ? selectedUnit.name : '';
});

const handleSubmit = () => {
    unitConversionStore.editUnitConversion(props.unit_conversion.slug, newUnitConversion.value);
}
</script>

<template>
    <AppLayout>
        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ desc }}
                            </p>
                        </div>

                        <form @submit.prevent="handleSubmit" class="max-w-full space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-4">
                                    <VSelect
                                        id="product_id"
                                        v-model="newUnitConversion.product_id"
                                        :options="products"
                                        label="Produk"
                                        placeholder="Pilih Produk"
                                        option-label="name"
                                        option-value="id"
                                        required
                                    />

                                    <VSelect
                                        id="from_unit_id"
                                        v-model="newUnitConversion.from_unit_id"
                                        :options="from_units"
                                        label="Satuan Dasar Produk"
                                        placeholder="Pilih Satuan"
                                        option-label="name"
                                        option-value="id"
                                        required
                                    />

                                    <VSelect
                                        id="to_unit_id"
                                        v-model="newUnitConversion.to_unit_id"
                                        :options="to_units"
                                        label="Konversi Ke-Satuan"
                                        placeholder="Pilih Satuan"
                                        option-label="name"
                                        option-value="id"
                                        required
                                    />

                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex-1">
                                            <TextNumber id="conversion_factor"
                                                v-model="newUnitConversion.conversion_factor" label="Nilai Konversi"
                                                placeholder="Masukkan Nilai Konversi" required class="w-full" />
                                        </div>
                                        <span v-if="selectedToUnitLabel"
                                            class="text-sm text-gray-600 dark:text-gray-200 italic ml-3">
                                            {{ selectedToUnitLabel }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link
                                    :href="route('admin.master.unit_conversion.index')"
                                    class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors"
                                >
                                    Batal
                                </Link>
                                <BaseButton
                                    :loading="unitConversionStore.isLoading"
                                    text="Ubah"
                                    loadingText="Mengubah..."
                                    color="orange"
                                />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
