<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Badge from '@/components/Badge.vue';
import Pagination from '@/components/Pagination.vue';
import { Eye, Pencil, Trash2, FileX, FileText, CircleCheckBig } from 'lucide-vue-next';
import { formatNumber } from '@/helpers/currencyHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';
import { StockOutPageProps } from '@/types/stock_out';
import { useStockOutStore } from '@/stores/stockOutStore';
import BadgeStockOutStatus from '@/components/BadgeStockOutStatus.vue';

const props = defineProps<StockOutPageProps>();
const stockOutStore = useStockOutStore();

onMounted(() => {
    stockOutStore.initializeFromProps(props);
});

watch(() => props.stock_outs, (newStockOuts) => {
    stockOutStore.stock_outs = newStockOuts;
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
                            <Link :href="route('admin.inventory.stock_out.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <FileText class="w-4 h-4 mr-2" />
                            Tambah Pengeluaran Barang
                            </Link>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="stockOutStore.searchQuery"
                                    placeholder="Cari berdasarkan nomor pengeluaran barang..."
                                    @update:modelValue="stockOutStore.setSearchQuery" />
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div v-if="stockOutStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data pengeluaran barang...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            No. Pengeluaran</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Notes</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Subtotal</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Diskon</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            PPN</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">
                                            Total Net</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </template>
                                <template #body>
                                    <tr v-if="stockOutStore.stock_outs.data.length === 0">
                                        <td colspan="11" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data purchase order yang ditemukan.</p>
                                                <button @click="stockOutStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(stock_out, index) in stockOutStore.stock_outs.data"
                                        :key="stock_out.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (stockOutStore.stock_outs.current_page - 1) *
                                                stockOutStore.stock_outs.per_page }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                            {{ stock_out.stock_out_number }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ stock_out.out_date }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1">
                                                <Badge :type="stock_out.is_active ? 'success' : 'danger'"
                                                    :text="stock_out.is_active ? 'Aktif' : 'Nonaktif'" />
                                                <BadgeInventory :type="stock_out.status" />
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1">
                                                <BadgeStockOutStatus :type="stock_out.status_notes" />
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_out.subtotal) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_out.discount) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ formatNumber((stock_out.subtotal -
                                                    stock_out.discount) *
                                                    (stock_out.tax / 100)) }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ formatNumber(stock_out.tax) }} %
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(stock_out.total_net) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link
                                                    :href="route('admin.inventory.stock_out.show', stock_out.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-purple-600 hover:text-white hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 transition-colors duration-150">
                                                <Eye class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="stock_out.status === 'PROSESS'"
                                                    :href="route('admin.inventory.stock_out.edit', stock_out.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-indigo-600 hover:text-white hover:bg-indigo-600 dark:text-indigo-400 dark:hover:text-white dark:hover:bg-indigo-500 transition-colors duration-150">
                                                <Pencil class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="stock_out.status === 'PROSESS'"
                                                    :href="route('admin.inventory.stock_out.approval.view', stock_out.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-green-600 hover:text-white hover:bg-green-600 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-500 transition-colors duration-150">
                                                <CircleCheckBig class="w-4 h-4" />
                                                </Link>

                                                <button v-if="stock_out.status === 'PROSESS'" @click="stockOutStore.deleteStockOut(stock_out)"
                                                    :disabled="stockOutStore.isLoading"
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
                            <Pagination :links="stockOutStore.stock_outs.links"
                                :current-page="stockOutStore.stock_outs.current_page"
                                :per-page="stockOutStore.stock_outs.per_page"
                                :total="stockOutStore.stock_outs.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
