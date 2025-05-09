<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Pagination from '@/components/Pagination.vue';
import { Eye, FileX } from 'lucide-vue-next';
import { formatNumber } from '@/helpers/currencyHelper';
import { StockCardPageProps } from '@/types/stock_card';
import { useStockCardStore } from '@/stores/stockCardStore';
import DateOnlyMonthInput from '@/components/DateOnlyMonthInput.vue';
import DateOnlyYearInput from '@/components/DateOnlyYearInput.vue';
import { getMonthName } from '@/helpers/dateStringHelper';
import BadgeStatusRunning from '@/components/BadgeStatusRunning.vue';

const props = defineProps<StockCardPageProps>();
const stockCardStore = useStockCardStore();

onMounted(() => {
    stockCardStore.initializeFromProps(props);
});

watch(() => props.stock_cards, (newStockCards) => {
    stockCardStore.stock_cards = newStockCards;
}, { deep: true });

</script>
<template>
    <AppLayout>

        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ desc }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar (Sisi Kiri) -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="stockCardStore.searchQuery"
                                    placeholder="Cari kode atau nama produk..."
                                    @update:modelValue="stockCardStore.setSearchQuery" />
                            </div>

                            <!-- Filter Month & Year (Sisi Kanan) -->
                            <div class="flex gap-4">
                                <div class="w-48">
                                    <DateOnlyMonthInput v-model="stockCardStore.monthFilter" placeholder="Filter Bulan"
                                        @update:model-value="(value) => stockCardStore.setMonthFilter(value || '')" />
                                </div>
                                <div class="w-48">
                                    <DateOnlyYearInput v-model="stockCardStore.yearFilter" placeholder="Filter Tahun"
                                        @update:model-value="(value) => stockCardStore.setYearFilter(value || '')" />
                                </div>
                            </div>
                        </div>


                        <!-- Loading Indicator -->
                        <div v-if="stockCardStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data kartu stock...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Periode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Unit</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Begining Balance</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            In Balance</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Out Balance</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Ending Balance</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </template>
                                <template #body>
                                    <tr v-if="stockCardStore.stock_cards.data.length === 0">
                                        <td colspan="11" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data kartu stock yang ditemukan.</p>
                                                <button @click="stockCardStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(stock_card, index) in stockCardStore.stock_cards.data"
                                        :key="stock_card.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (stockCardStore.stock_cards.current_page - 1) *
                                                stockCardStore.stock_cards.per_page }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ stock_card.product?.name || '' }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Kode: {{
                                                    stock_card.product?.code || '' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ stock_card.year || '' }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{
                                                    getMonthName(stock_card.month) || '' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ stock_card.product?.defaultUnit?.name || ''
                                                }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Kode: {{
                                                    stock_card.product?.defaultUnit?.code || '' }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            <BadgeStatusRunning :status="stock_card.status_running" />
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_card.beginning_balance) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_card.in_balance) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_card.out_balance) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_card.ending_balance) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link :href="route('admin.inventory.stock_card.show', stock_card.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-purple-600 hover:text-white hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 transition-colors duration-150">
                                                <Eye class="w-4 h-4" />
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="stockCardStore.stock_cards.links"
                                :current-page="stockCardStore.stock_cards.current_page"
                                :per-page="stockCardStore.stock_cards.per_page"
                                :total="stockCardStore.stock_cards.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
