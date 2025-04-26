<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import CheckboxInput from '@/components/CheckboxInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import { useProductStore } from '@/stores/productStore';
import TextNumber from '@/components/TextNumber.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import VSelect from '@/components/VSelect.vue';
import { Unit } from '@/types/unit';

const props = defineProps<{
    title: string;
    desc: string;
    units: Unit[];
}>();

const productStore = useProductStore();

const newProduct = ref({
    code: '',
    name: '',
    variant_name: '',
    default_unit_id: 0,
    purchase_price: 0,
    selling_price: 0,
    description: '',
    is_active: true
});

const handleSubmit = () => {
    productStore.createProduct(newProduct.value);
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
                                    <TextInput
                                        id="code"
                                        v-model="newProduct.code"
                                        label="Kode Produk"
                                        placeholder="Masukkan Kode Produk"
                                        required
                                    />

                                    <TextInput
                                        id="name"
                                        v-model="newProduct.name"
                                        label="Nama Produk"
                                        placeholder="Masukkan Nama Produk"
                                        required
                                    />

                                    <TextInput
                                        id="variant_name"
                                        v-model="newProduct.variant_name"
                                        label="Nama Variant"
                                        placeholder="Masukkan Nama Varian"
                                        required
                                    />
                                </div>

                                <div class="space-y-4">
                                    <TextNumber
                                        id="purchase_price"
                                        v-model="newProduct.purchase_price"
                                        label="Harga Beli"
                                        placeholder="Masukkan Harga Beli"
                                        required
                                    />

                                    <TextNumber
                                        id="selling_price"
                                        v-model="newProduct.selling_price"
                                        label="Harga Jual"
                                        placeholder="Masukkan Harga Jual"
                                        required
                                    />

                                    <VSelect
                                        id="default_unit_id"
                                        v-model="newProduct.default_unit_id"
                                        :options="units"
                                        label="Satuan Default"
                                        placeholder="Pilih Satuan Default"
                                        option-label="name"
                                        option-value="id"
                                        required
                                    />
                                </div>
                            </div>


                            <TextAreaInput
                                v-model="newProduct.description"
                                id="Deskripsi Produk"
                                label="Deskripsi Produk"
                                placeholder="Masukkan Deskripsi Produk"
                                :rows="6"
                                :maxlength="500"
                                required
                            />

                            <CheckboxInput
                                id="is_active"
                                v-model="newProduct.is_active"
                                label="Satuan Aktif"
                            />

                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link
                                    :href="route('admin.master.product.index')"
                                    class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors"
                                >
                                    Batal
                                </Link>
                                <BaseButton :loading="productStore.isLoading" text="Simpan" loadingText="Menyimpan..." color="blue" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
