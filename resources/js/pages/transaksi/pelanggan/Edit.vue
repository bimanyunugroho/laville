<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import CheckboxInput from '@/components/CheckboxInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import { useCustomerStore } from '@/stores/customerStore';
import { Customer } from '@/types/customer';

const props = defineProps<{
    title: string;
    desc: string;
    customer: Customer;
}>();

const customerStore = useCustomerStore();

const newCustomer = ref({
    name: props.customer.name,
    phone: props.customer.phone,
    is_active: props.customer.is_active
});

const handleSubmit = () => {
    customerStore.editCustomer(props.customer.slug, newCustomer.value);
};
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
                                id="name"
                                v-model="newCustomer.name"
                                label="Nama Pelanggan"
                                placeholder="Masukkan Nama Pelanggan"
                                required
                            />

                            <TextInput
                                id="phone"
                                v-model="newCustomer.phone"
                                label="No.Telp"
                                placeholder="Masukkan No.Telp (Optional)"
                            />

                            <CheckboxInput
                                id="is_active"
                                v-model="newCustomer.is_active"
                                label="Satuan Aktif"
                            />

                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link
                                    :href="route('admin.transaksi.customer.index')"
                                    class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors"
                                >
                                    Batal
                                </Link>
                                <BaseButton :loading="customerStore.isLoading" text="Ubah" loadingText="Mengubah..." color="orange" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
