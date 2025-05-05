<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Package, Info, AlertTriangle, Truck, Calendar, User, FileText, CreditCard, Ban, CheckCircle, CalendarDays } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { StockOut } from '@/types/stock_out';
import { formatDate } from '@/helpers/dateStringHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';
import Badge from '@/components/Badge.vue';

const props = defineProps<{
    title: string;
    desc: string;
    stock_out: StockOut;
}>();
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
                                <Link v-if="stock_out.status === 'PROSESS'"
                                    :href="route('admin.inventory.stock_out.edit', stock_out.slug)" class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                                    bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                    shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                                    focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Pengeluaran Barang
                                </Link>
                                <Link :href="route('admin.inventory.stock_out.index')" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300
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

                        <!-- Status Badge -->
                        <div class="mb-6">
                            <div class="flex items-center space-x-2">
                                <Badge :type="stock_out.is_active ? 'success' : 'danger'"
                                    :text="stock_out.is_active ? 'Aktif' : 'Nonaktif'" />

                                <BadgeInventory :type="stock_out.status" />
                            </div>
                        </div>

                        <!-- Info Sections -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <!-- Left Column: Basic Info -->
                            <div
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <FileText class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" />
                                    Informasi Pengeluaran Barang
                                </h2>

                                <div class="space-y-3 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-1">
                                            <FileText class="h-4 w-4" />
                                            <span>No. Pengeluaran:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ stock_out.stock_out_number }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-1">
                                            <CalendarDays class="h-4 w-4" />
                                            <span>Tanggal Dibuat:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ formatDate(stock_out.out_date) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between" v-if="stock_out.ack_date">
                                        <div class="flex items-center space-x-1">
                                            <CheckCircle class="h-4 w-4" />
                                            <span>Tanggal Persetujuan:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ formatDate(stock_out.ack_date) }}
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between" v-if="stock_out.reject_date">
                                        <div class="flex items-center space-x-1">
                                            <XCircle class="h-4 w-4" />
                                            <span>Tanggal Penolakan:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ formatDate(stock_out.reject_date) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: People Info -->
                            <div
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <User class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" />
                                    Informasi Personal
                                </h2>

                                <div class="space-y-3">
                                    <div
                                        class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-1">
                                            <User class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                            <span>Dibuat Oleh:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ stock_out.user?.name || '—' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-1">
                                            <Truck class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                            <span>Supplier:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ stock_out.supplier?.name || 'Tanpa Supplier' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-1">
                                            <CheckCircle class="h-4 w-4 text-gray-500 dark:text-green-400" />
                                            <span>Disetujui Oleh:</span>
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ stock_out.userAck?.name || '—' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center space-x-1">
                                            <Ban class="h-4 w-4 text-gray-500 dark:text-red-400" />
                                            <span>Ditolak Oleh:</span>
                                        </div>
                                        <span class="font-mono text-gray-900 dark:text-white">
                                            {{ stock_out.userReject?.name || '—' }}
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div v-if="stock_out.notes"
                            class="mb-8 bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border border-gray-200 dark:border-gray-600">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                                <Info class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" />
                                Catatan
                            </h2>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ stock_out.notes }}</p>
                        </div>

                        <!-- Status Notes -->
                        <div v-if="stock_out.status_notes"
                            class="mb-8 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-5 border border-yellow-200 dark:border-yellow-800">
                            <h2 class="text-lg font-medium text-yellow-800 dark:text-yellow-200 mb-2 flex items-center">
                                <AlertTriangle class="h-5 w-5 mr-2 text-yellow-500" />
                                Catatan Status
                            </h2>
                            <p class="text-yellow-700 dark:text-yellow-300 whitespace-pre-line">{{
                                stock_out.status_notes }}</p>
                        </div>

                        <!-- Products Table -->
                        <div class="mb-8">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                <Package class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" />
                                Detail Barang
                            </h2>

                            <div class="overflow-x-auto rounded-lg shadow">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left font-medium tracking-wide">Nama
                                                Produk</th>
                                            <th class="px-4 py-2.5 text-left font-medium tracking-wide">Satuan
                                            </th>
                                            <th class="px-4 py-2.5 text-right font-medium tracking-wide">
                                                Kuantitas</th>
                                            <th class="px-4 py-2.5 text-right font-medium tracking-wide">Stok
                                                Terkini</th>
                                            <th class="px-4 py-2.5 text-left font-medium tracking-wide">
                                                Catatan</th>
                                            <th class="px-4 py-2.5 text-right font-medium tracking-wide">Harga
                                            </th>
                                            <th class="px-4 py-2.5 text-right font-medium tracking-wide">
                                                Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(detail, index) in stock_out.details" :key="index">
                                            <td class="px-3 py-2 text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ detail.product?.name }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                {{ detail.unit?.name }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ formatNumber(detail.quantity) }} {{ detail.unit?.name }}<br />
                                                <span class="text-xs text-gray-500">@{{
                                                    formatNumber(detail.base_quantity) }} Base</span>
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ formatNumber(detail.product?.currentStocks?.[0]?.quantity ?? 0) }} {{
                                                    detail.unit?.name }}<br />
                                                <span class="text-xs text-gray-500">@ {{
                                                    formatNumber(detail.product?.currentStocks?.[0]?.base_quantity ?? 0)
                                                }}</span>
                                            </td>
                                            <td
                                                class="px-3 py-2 text-left text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ detail.notes_detail }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ formatCurrency(detail.price) }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ formatCurrency(detail.subtotal) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div v-if="!stock_out.details || stock_out.details.length === 0"
                                    class="text-center py-6 text-gray-500 dark:text-gray-400 italic">
                                    Tidak ada data detail produk
                                </div>
                            </div>

                        </div>

                        <!-- Summary -->
                        <div class="mb-6">
                            <div
                                class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5 border border-gray-200 dark:border-gray-600">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                                    <CreditCard class="h-5 w-5 mr-2 text-gray-500 dark:text-gray-400" />
                                    Ringkasan Biaya
                                </h2>

                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Subtotal:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(stock_out.subtotal) }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Pajak:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(stock_out.tax) }}</span>
                                    </div>

                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Diskon:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(stock_out.discount) }}</span>
                                    </div>

                                    <div
                                        class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                                        <span class="font-medium text-gray-900 dark:text-white">Total:</span>
                                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{
                                            formatCurrency(stock_out.total_net) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
