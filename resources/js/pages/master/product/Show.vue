<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useProductStore } from '@/stores/productStore';
import { Unit } from '@/types/unit';
import { Product } from '@/types/produk';
import { formatCurrency } from '@/helpers/currencyHelper';
import {
    Package2,
    Tag,
    VariableIcon,
    Ruler,
    DollarSign,
    ShoppingCart,
    FileText,
    Check,
    X,
    ArrowLeft
} from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    desc: string;
    product: Product;
    units: Unit[];
}>();

const productStore = useProductStore();

const statusClass = computed(() => {
    return props.product.is_active
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
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>
                            <Link :href="route('admin.master.product.index')" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700
                      dark:hover:bg-gray-600 rounded-lg transition-colors text-sm font-medium">
                            <ArrowLeft class="w-4 h-4" />
                            Kembali
                            </Link>
                        </div>

                        <!-- Product Information Card -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 mb-6">
                            <div class="flex flex-col md:flex-row gap-6">
                                <!-- Left Column (Basic Info) -->
                                <div class="flex-1 space-y-4">
                                    <div class="flex items-start">
                                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2 mr-3">
                                            <Package2 class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Kode Produk</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ product.code }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-purple-100 dark:bg-purple-900 rounded-lg p-2 mr-3">
                                            <Tag class="w-5 h-5 text-purple-600 dark:text-purple-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Nama Produk</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{ product.name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-pink-100 dark:bg-pink-900 rounded-lg p-2 mr-3">
                                            <VariableIcon class="w-5 h-5 text-pink-600 dark:text-pink-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Nama Variant</div>
                                            <div class="font-medium text-gray-800 dark:text-white">{{
                                                product.variant_name }}</div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-green-100 dark:bg-green-900 rounded-lg p-2 mr-3">
                                            <Ruler class="w-5 h-5 text-green-600 dark:text-green-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Satuan Dasar</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ product.defaultUnit ? product.defaultUnit.name : 'Tidak ada' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column (Pricing & Status) -->
                                <div class="flex-1 space-y-4">
                                    <div class="flex items-start">
                                        <div class="bg-teal-100 dark:bg-teal-900 rounded-lg p-2 mr-3">
                                            <ShoppingCart class="w-5 h-5 text-teal-600 dark:text-teal-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Harga Beli</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ formatCurrency(product.purchase_price) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div class="bg-amber-100 dark:bg-amber-900 rounded-lg p-2 mr-3">
                                            <DollarSign class="w-5 h-5 text-amber-600 dark:text-amber-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Harga Jual</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ formatCurrency(product.selling_price) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start">
                                        <div :class="[
                                            'rounded-lg p-2 mr-3',
                                            product.is_active ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900'
                                        ]">
                                            <component :is="product.is_active ? Check : X" :class="[
                                                'w-5 h-5',
                                                product.is_active ? 'text-green-600 dark:text-green-300' : 'text-red-600 dark:text-red-300'
                                            ]" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="statusClass">
                                                {{ product.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Card -->
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                            <div class="flex items-center mb-4">
                                <FileText class="w-5 h-5 text-gray-600 dark:text-gray-300 mr-2" />
                                <h3 class="text-lg font-medium text-gray-800 dark:text-white">Deskripsi Produk</h3>
                            </div>
                            <div class="prose dark:prose-invert max-w-none">
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                    {{ product.description || 'Tidak ada deskripsi' }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-3 pt-6">
                            <Link :href="route('admin.master.product.edit', product.slug)" class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                      bg-blue-600 px-4 py-2 text-sm font-medium text-white
                      shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                      focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Edit Produk
                            </Link>
                            <Link :href="route('admin.master.product.index')" class="inline-flex justify-center rounded-md border border-gray-300
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
        </div>
    </AppLayout>
</template>
