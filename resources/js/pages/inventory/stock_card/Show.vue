<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Badge from '@/components/Badge.vue';
import { formatDate } from '@/helpers/dateStringHelper';
import { StockCard, StockCardDetail } from '@/types/stock_card';
import { getMonthName } from '@/helpers/dateStringHelper';
import BadgeStatusRunning from '@/components/BadgeStatusRunning.vue';
import BadgeMovementType from '@/components/BadgeMovementType.vue';
import BadgeReferenceStatus from '@/components/BadgeReferenceStatus.vue';
import { defaultNavigator } from '@vueuse/core';
import { formatterSplitLabel } from '@/helpers/formatterSplitLabel';
import { Dock, FileArchive } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    desc: string;
    stock_card: StockCard;
}>();

const movementTypeLabels: Record<string, { text: string, color: string }> = {
    'MASUK': { text: 'Masuk', color: 'green' },
    'KELUAR': { text: 'Keluar', color: 'red' },
    'MASTER_BARU': { text: 'Master Baru', color: 'yellow' },
    'PROSESS': { text: 'Prosess (PO)', color: 'blue' },
};

const referenceTypeLabels: Record<string, string> = {
    'MASTER_BARU': 'Master Baru',
    'PEMBELIAN_BARANG': 'Pembelian Barang (PO)',
    'PENJUALAN': 'Transaksi Penjualan',
    'PENERIMAAN_BARANG': 'Penerimaan Barang',
    'PENGELUARAN_BARANG': 'Pengeluaran Barang',
    'STOCK_OPNAME': 'Stock Opname',
};

const sortedStockCardDetails = computed(() => {
    if (!props.stock_card.stockCardDetails) return [];

    return [...props.stock_card.stockCardDetails].sort((a, b) => {
        return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
    });
});

const getUnitName = (detail: StockCardDetail) => {
    return detail.unit?.name || 'N/A';
};


const getReferenceTypeLabel = (type: string) => {
    return referenceTypeLabels[type] || type;
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

                                <div class="hidden sm:flex items-center space-x-3">
                                    <Link :href="route('admin.inventory.stock_card.index')" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300
                                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                        focus:ring-primary-500 focus:ring-offset-2
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                        dark:hover:bg-gray-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Back to List
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Card Information Card -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Product Information -->
                                <div class="col-span-1">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informati Produk</h2>
                                    <dl class="space-y-2">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Produk:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ stock_card.product?.code || 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Produk:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ stock_card.product?.name || 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Variant:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ stock_card.product?.variant_name || 'N/A' }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Satuan Dasar:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ stock_card.product?.defaultUnit?.name || 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Stock Card Information -->
                                <div class="col-span-1">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Kartu Stok</h2>
                                    <dl class="space-y-2">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ getMonthName(stock_card.month) }} {{ stock_card.year }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                                            <dd class="text-sm">
                                                <BadgeStatusRunning :status="stock_card.status_running" />
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">ID Kartu Stok:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white">{{ stock_card.id || 'N/A' }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Balance Information -->
                                <div class="col-span-1">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Saldo</h2>
                                    <dl class="space-y-2">
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Awal:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white font-medium">
                                                {{ stock_card.beginning_balance }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Masuk:</dt>
                                            <dd class="text-sm text-green-600 dark:text-green-400 font-medium">
                                                + {{ stock_card.in_balance }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Saldo Keluar:</dt>
                                            <dd class="text-sm text-red-600 dark:text-red-400 font-medium">
                                                - {{ stock_card.out_balance }}
                                            </dd>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Saldo Akhir:</dt>
                                            <dd class="text-sm text-gray-900 dark:text-white font-bold">
                                                {{ stock_card.ending_balance }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Card Details Table -->
                        <div class="mt-8">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Stock Transaction History</h2>

                            <div class="overflow-x-auto mt-4">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tipe
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Jumlah
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Saldo
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Keterangan
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-if="!stock_card.stockCardDetails || stock_card.stockCardDetails.length === 0" class="bg-white dark:bg-gray-800">
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No stock transaction records found.
                                            </td>
                                        </tr>
                                        <tr v-for="detail in sortedStockCardDetails" :key="detail.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                <div class="flex flex-col">
                                                    <span class="font-medium"> {{ formatDate(detail.transaction_date) }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ getReferenceTypeLabel(detail.reference_type) }} #{{ detail.reference_id }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ formatterSplitLabel(detail.reference_status) }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ formatterSplitLabel(detail.movement_type) }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span :class="{
                                                    'text-green-600 dark:text-green-400': detail.movement_type === 'MASUK',
                                                    'text-red-600 dark:text-red-400': detail.movement_type === 'KELUAR',
                                                    'text-gray-500 dark:text-gray-400': !['MASUK', 'KELUAR'].includes(detail.movement_type)
                                                }">
                                                    {{ detail.movement_type === 'MASUK' ? '+' : detail.movement_type === 'KELUAR' ? '-' : '' }}
                                                    {{ detail.quantity }} {{ getUnitName(detail) }}
                                                    <span v-if="detail.base_quantity !== detail.quantity" class="text-xs ml-1">
                                                        ({{ detail.base_quantity }} base)
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ detail.balance_quantity }} {{ getUnitName(detail) }}
                                                <span v-if="detail.balance_base_quantity !== detail.balance_quantity" class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                                                    ({{ detail.balance_base_quantity }} base)
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                <div class="flex items-center space-x-2">
                                                    <FileArchive class="w-4 h-4" />
                                                    <span class="truncate">{{ detail.notes || '-' }}</span>
                                                </div>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
