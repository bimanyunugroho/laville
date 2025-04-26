<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Boxes,
    Tag,
    Phone,
    Mail,
    FileText,
    Check,
    X,
    ArrowLeft
} from 'lucide-vue-next';
import { Supplier } from '@/types/supplier';
import { useSupplierStore } from '@/stores/supplierStore';

const props = defineProps<{
    title: string;
    desc: string;
    supplier: Supplier;
}>();

const supplierStore = useSupplierStore();

const statusClass = computed(() => {
    return props.supplier.is_active
        ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
        : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
});
</script>

<template>
    <AppLayout>

        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header with title and back button -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ title }}
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ desc }}
                                    </p>
                                </div>

                                <div class="hidden sm:block">
                                    <div class="flex items-center space-x-3">
                                        <Link :href="route('admin.inventory.supplier.edit', supplier.slug)"
                                            class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                                        bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                        shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                                        focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                        Edit Supplier
                                        </Link>
                                        <Link :href="route('admin.inventory.supplier.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                        focus:ring-primary-500 focus:ring-offset-2
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                        dark:hover:bg-gray-600 transition-colors">
                                        Kembali
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Information Card -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 mb-6">
                            <div class="flex flex-col md:flex-row gap-6">
                                <!-- Left Column (Basic Info) -->
                                <div class="flex-1 space-y-4">
                                    <div class="flex items-start">
                                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2 mr-3">
                                            <Boxes class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Kode Supplier</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ supplier.code }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-purple-100 dark:bg-purple-900 rounded-lg p-2 mr-3">
                                            <Tag class="w-5 h-5 text-purple-600 dark:text-purple-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Nama Supplier</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ supplier.name }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-pink-100 dark:bg-pink-900 rounded-lg p-2 mr-3">
                                            <Phone class="w-5 h-5 text-pink-600 dark:text-pink-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">No. Telp</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ supplier.phone }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column (Contact & Status) -->
                                <div class="flex-1 space-y-4">
                                    <div class="flex items-start">
                                        <div class="bg-yellow-100 dark:bg-yellow-900 rounded-lg p-2 mr-3">
                                            <Mail class="w-5 h-5 text-yellow-600 dark:text-yellow-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Email</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ supplier.email }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div :class="[
                                            'rounded-lg p-2 mr-3',
                                            supplier.is_active ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900'
                                        ]">
                                            <component :is="supplier.is_active ? Check : X" :class="[
                                                'w-5 h-5',
                                                supplier.is_active ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300'
                                            ]" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="statusClass">
                                                {{ supplier.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Card -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <FileText class="w-5 h-5 text-gray-600 dark:text-gray-300 mr-2" />
                                <h3 class="text-lg font-medium text-gray-800 dark:text-white">Alamat</h3>
                            </div>
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                    {{ supplier.address || 'Tidak ada alamat' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
