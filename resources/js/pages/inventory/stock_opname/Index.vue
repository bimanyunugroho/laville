<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Pagination from '@/components/Pagination.vue';
import { CircleCheckBig, Eye, FileText, FileX, Pencil, ShieldCheck, Trash2 } from 'lucide-vue-next';
import { formatNumber } from '@/helpers/currencyHelper';
import DateOnlyMonthInput from '@/components/DateOnlyMonthInput.vue';
import DateOnlyYearInput from '@/components/DateOnlyYearInput.vue';
import { getMonthName } from '@/helpers/dateStringHelper';
import { StockOpnamePageProps } from '@/types/stock_opname';
import { useStockOpnameStore } from '@/stores/stockOpnameStore';
import Badge from '@/components/Badge.vue';
import BadgeStockOpname from '@/components/BadgeStockOpname.vue';

const props = defineProps<StockOpnamePageProps>();
const stockOpnameStore = useStockOpnameStore();

onMounted(() => {
    stockOpnameStore.initializeFromProps(props);
});

watch(() => props.stock_opnames, (newStockOpnames) => {
    stockOpnameStore.stock_opnames = newStockOpnames;
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
                            <Link :href="route('admin.inventory.stock_opname.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <FileText class="w-4 h-4 mr-2" />
                            Tambah Stock Opname
                            </Link>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar (Sisi Kiri) -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="stockOpnameStore.searchQuery"
                                    placeholder="Cari Nomor Stock Opname..."
                                    @update:modelValue="stockOpnameStore.setSearchQuery" />
                            </div>

                            <!-- Filter Month & Year (Sisi Kanan) -->
                            <div class="flex gap-4">
                                <div class="w-48">
                                    <DateOnlyMonthInput v-model="stockOpnameStore.monthFilter"
                                        placeholder="Filter Bulan"
                                        @update:model-value="(value) => stockOpnameStore.setMonthFilter(value || '')" />
                                </div>
                                <div class="w-48">
                                    <DateOnlyYearInput v-model="stockOpnameStore.yearFilter" placeholder="Filter Tahun"
                                        @update:model-value="(value) => stockOpnameStore.setYearFilter(value || '')" />
                                </div>
                            </div>
                        </div>


                        <!-- Loading Indicator -->
                        <div v-if="stockOpnameStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data stock opname...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            No. SO</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Periode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Subtotal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Diskon</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tax</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Total Net</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </template>
                                <template #body>
                                    <tr v-if="stockOpnameStore.stock_opnames.data.length === 0">
                                        <td colspan="11" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data stock opname yang ditemukan.</p>
                                                <button @click="stockOpnameStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(stock_opname, index) in stockOpnameStore.stock_opnames.data"
                                        :key="stock_opname.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (stockOpnameStore.stock_opnames.current_page - 1) *
                                                stockOpnameStore.stock_opnames.per_page }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ stock_opname.so_number }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                            <span class="font-medium">{{ stock_opname.year || '' }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{
                                                    getMonthName(stock_opname.month) || '' }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1">
                                                <Badge :type="stock_opname.is_active ? 'success' : 'danger'"
                                                    :text="stock_opname.is_active ? 'Aktif' : 'Nonaktif'" />
                                                <BadgeStockOpname :type="stock_opname.status" />
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_opname.subtotal) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_opname.discount) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_opname.tax) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_opname.total_net) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link
                                                    :href="route('admin.inventory.stock_opname.show', stock_opname.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-purple-600 hover:text-white hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 transition-colors duration-150">
                                                <Eye class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="stock_opname.status === 'DRAFT'"
                                                    :href="route('admin.inventory.stock_opname.edit', stock_opname.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-indigo-600 hover:text-white hover:bg-indigo-600 dark:text-indigo-400 dark:hover:text-white dark:hover:bg-indigo-500 transition-colors duration-150">
                                                <Pencil class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="stock_opname.status === 'DRAFT'"
                                                    :href="route('admin.inventory.stock_opname.validator.view', stock_opname.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-orange-600 hover:text-white hover:bg-orange-600 dark:text-orange-400 dark:hover:text-white dark:hover:bg-orange-500 transition-colors duration-150">
                                                <ShieldCheck class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="stock_opname.status === 'VALIDATED'"
                                                    :href="route('admin.inventory.stock_opname.approval.view', stock_opname.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-green-600 hover:text-white hover:bg-green-600 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-500 transition-colors duration-150">
                                                <CircleCheckBig class="w-4 h-4" />
                                                </Link>

                                                <button v-if="stock_opname.status === 'DRAFT'" @click="stockOpnameStore.deleteStockOpname(stock_opname)"
                                                    :disabled="stockOpnameStore.isLoading"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-red-600 hover:text-white hover:bg-red-600 dark:text-red-400 dark:hover:text-white dark:hover:bg-red-500 transition-colors duration-150 disabled:opacity-50 cursor-pointer">
                                                    <Trash2 class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="stockOpnameStore.stock_opnames.links"
                                :current-page="stockOpnameStore.stock_opnames.current_page"
                                :per-page="stockOpnameStore.stock_opnames.per_page"
                                :total="stockOpnameStore.stock_opnames.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
