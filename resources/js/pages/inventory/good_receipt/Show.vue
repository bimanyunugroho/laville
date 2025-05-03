<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Package, Info, AlertTriangle, } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { GoodReceipt, GoodReceiptDetail } from '@/types/good_receipt';

const props = defineProps<{
    title: string;
    desc: string;
    good_receipt: GoodReceipt;
}>();

// Status badge styling
const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'PROSESS':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'RECEIVED':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'CANCELED':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    }
};

// Format date
const formatDate = (dateString: string | null) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <AppLayout>

        <Head :title="title" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                            <!-- Left: Title and Description -->
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>

                            <!-- Right: Action Buttons -->
                            <div class="flex items-center space-x-3">
                                <Link v-if="good_receipt.status_receipt === 'PROSESS'"
                                    :href="route('admin.inventory.good_receipt.edit', good_receipt.slug)" class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                                    bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                    shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                                    focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Penerimaan Barang
                                </Link>
                                <Link :href="route('admin.inventory.good_receipt.index')" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300
                                    bg-white px-4 py-2 text-sm font-medium text-gray-700
                                    shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                    focus:ring-primary-500 focus:ring-offset-2
                                    dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                    dark:hover:bg-gray-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to List
                                </Link>
                            </div>
                        </div>

                        <!-- Receipt Information -->
                        <div class="mb-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Left Column: Basic Info -->
                            <div class="col-span-1 lg:col-span-2">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        Informasi Penerimaan
                                    </h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nomor
                                                    Penerimaan</span>
                                                <span
                                                    class="block text-base font-medium text-gray-900 dark:text-white">{{
                                                        good_receipt.receipt_number }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal
                                                    Penerimaan</span>
                                                <span class="block text-base text-gray-900 dark:text-white">{{
                                                    formatDate(good_receipt.receipt_date) }}</span>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nomor
                                                    PO</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.purchaseOrder?.po_number || '-' }}
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.supplier?.name || '-' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1"
                                                    :class="getStatusBadgeClass(good_receipt.status_receipt)">
                                                    {{ good_receipt.status_receipt }}
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat
                                                    Oleh</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.user?.name || '-' }}
                                                </span>
                                            </div>
                                            <div class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal
                                                    Dibuat</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ formatDate(good_receipt.created_at) }}
                                                </span>
                                            </div>
                                            <div v-if="good_receipt.status_receipt === 'RECEIVED'" class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Dikonfirmasi
                                                    Oleh</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.userAck?.name || '-' }}
                                                </span>
                                            </div>
                                            <div v-if="good_receipt.status_receipt === 'CANCELED'" class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Dibatalkan
                                                    Oleh</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.userReject?.name || '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Financial Summary -->
                            <div class="col-span-1">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 h-full">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                        Ringkasan
                                    </h2>
                                    <div class="space-y-3">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Total</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                formatCurrency(good_receipt.subtotal) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Diskon</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                formatCurrency(good_receipt.discount) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                formatCurrency(good_receipt.subtotal - good_receipt.discount) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Pajak # {{
                                                formatNumber(good_receipt.tax) }}%</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                formatCurrency((good_receipt.subtotal - good_receipt.discount) *
                                                    (good_receipt.tax / 100)) }}</span>
                                        </div>
                                        <div class="pt-3 border-t border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-between">
                                                <span
                                                    class="text-base font-medium text-gray-900 dark:text-white">Total</span>
                                                <span class="text-base font-bold text-gray-900 dark:text-white">{{
                                                    formatCurrency(good_receipt.total_net) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-8">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                    <div class="flex items-center">
                                        <Info class="w-5 h-5 mr-1" />
                                        Catatan
                                    </div>
                                </h2>
                                <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                    {{ good_receipt.notes || 'Tidak ada catatan' }}
                                </p>
                            </div>
                        </div>

                        <!-- Product Details Table -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <Package class="w-5 h-5 mr-2" />
                                Detail Barang
                            </h2>

                            <template v-for="(detail, index) in good_receipt.details" :key="detail.id">
                                <div v-if="detail.received_quantity !== detail.quantity" :class="[
                                    detail.received_quantity > detail.quantity
                                        ? 'bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-md border border-red-200 dark:border-red-800'
                                        : '',
                                    detail.received_quantity < detail.quantity
                                        ? 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 rounded-md border border-yellow-200 dark:border-yellow-800'
                                        : '',
                                    detail.received_quantity === detail.quantity
                                        ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 rounded-md border border-green-200 dark:border-green-800'
                                        : ''
                                ]" class="mb-4 p-3 flex items-center">
                                    <AlertTriangle class="w-5 h-5 mr-2" />
                                    <span>
                                        Barang <strong>{{ detail.product?.name }}</strong> belum diterima sepenuhnya.
                                    </span>
                                </div>
                            </template>


                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                No
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Produk
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Unit
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Qty Dipesan
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Qty Diterima
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Sisa
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Harga
                                            </th>
                                            <th scope="col"
                                                class="px-3 py-3.5 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-if="!good_receipt.details || good_receipt.details.length === 0">
                                            <td colspan="7"
                                                class="px-3 py-4 text-sm text-center text-gray-500 dark:text-gray-400">
                                                Tidak ada data detail
                                            </td>
                                        </tr>
                                        <tr v-for="(detail, index) in good_receipt.details" :key="detail.id"
                                            class="hover:bg-gray-50 dark:hover:bg-gray-700" :class="[
                                                detail.received_quantity > detail.quantity ? 'bg-red-50 dark:bg-red-900/30' : '',
                                                detail.received_quantity < detail.quantity ? 'bg-yellow-50 dark:bg-yellow-900/20' : '',
                                                detail.received_quantity === detail.quantity ? 'bg-green-50 dark:bg-green-900/20' : ''
                                            ]">
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ index + 1 }}
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                <div class="font-medium text-gray-900 dark:text-white">{{
                                                    detail.product?.name }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ detail.product_id }}</div>
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ detail.unit?.name }}
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ detail.purchaseOrderDetail?.quantity }}
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ detail.received_quantity }}
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ detail.quantity - detail.received_quantity }}
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400">
                                                {{ formatCurrency(detail.price) }}
                                            </td>
                                            <td
                                                class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white font-medium">
                                                {{ formatCurrency(detail.subtotal) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <td colspan="7"
                                                class="px-3 py-3.5 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                Total
                                            </td>
                                            <td
                                                class="px-3 py-3.5 text-right text-sm font-bold text-gray-900 dark:text-white">
                                                {{ formatCurrency(good_receipt.subtotal) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
