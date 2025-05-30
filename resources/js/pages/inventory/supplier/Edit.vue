<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import CheckboxInput from '@/components/CheckboxInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import { useSupplierStore } from '@/stores/supplierStore';
import TextAreaInput from '@/components/TextAreaInput.vue';
import { Supplier } from '@/types/supplier';

const props = defineProps<{
    title: string;
    desc: string;
    supplier: Supplier;
}>();

const supplierStore = useSupplierStore();

const newSupplier = ref({
    code: props.supplier.code,
    name: props.supplier.name,
    phone: props.supplier.phone,
    email: props.supplier.email,
    address: props.supplier.address,
    is_active: props.supplier.is_active
});

const handleSubmit = () => {
    supplierStore.editSupplier(props.supplier.slug, newSupplier.value);
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
                            <TextInput
                                id="code"
                                v-model="newSupplier.code"
                                label="Kode Supplier"
                                placeholder="Masukkan kode supplier"
                                required
                            />

                            <TextInput
                                id="name"
                                v-model="newSupplier.name"
                                label="Nama Supplier"
                                placeholder="Masukkan nama supplier"
                                required
                            />

                            <TextInput
                                id="phone"
                                v-model="newSupplier.phone"
                                label="No. Telp"
                                placeholder="Masukkan no.telp"
                                required
                            />

                            <TextInput
                                id="email"
                                v-model="newSupplier.email"
                                label="Email"
                                placeholder="Masukkan Email"
                                required
                            />

                            <TextAreaInput
                                v-model="newSupplier.address"
                                id="address"
                                label="Alamat Supplier"
                                placeholder="Masukkan Alamat Supplier"
                                :rows="6"
                                :maxlength="500"
                                required
                            />

                            <CheckboxInput
                                id="is_active"
                                v-model="newSupplier.is_active"
                                label="Satuan Aktif"
                            />

                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link
                                    :href="route('admin.inventory.supplier.index')"
                                    class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors"
                                >
                                    Batal
                                </Link>
                                <BaseButton :loading="supplierStore.isLoading" text="Simpan" loadingText="Mengubah..." color="orange" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
