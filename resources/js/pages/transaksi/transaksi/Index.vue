<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Pagination from '@/components/Pagination.vue';
import { Eye, FileText, FileX, Pencil, Trash2 } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { TransactionPageProps } from '@/types/transaction';
import { useTransactionStore } from '@/stores/transactionStore';
import BadgeStatusTransaction from '@/components/BadgeStatusTransaction.vue';
import BadgeTypeSourceTransaction from '@/components/BadgeTypeSourceTransaction.vue';

const props = defineProps<TransactionPageProps>();
const transactionStore = useTransactionStore();

onMounted(() => {
    transactionStore.initializeFromProps(props);
});

watch(() => props.transactions, (newTransactions) => {
    transactionStore.transactions = newTransactions;
}, {deep: true});


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
                            <Link :href="route('admin.transaksi.transaction.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <FileText class="w-4 h-4 mr-2" />
                            Tambah Transaksi
                            </Link>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar (Sisi Kiri) -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="transactionStore.searchQuery"
                                    placeholder="Cari Nomor Invoice..."
                                    @update:modelValue="transactionStore.setSearchQuery" />
                            </div>

                            <!-- Filter Month & Year (Sisi Kanan) -->
                            <div class="flex gap-4">
                                <div class="w-48">

                                </div>
                                <div class="w-48">

                                </div>
                            </div>
                        </div>


                        <!-- Loading Indicator -->
                        <div v-if="transactionStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data transaksi...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            No. Invoice</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Pelanggan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tgl. Transaksi</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Kasir</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Total</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Diskon</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Subtotal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tax</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Total Net</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </template>
                                <template #body>
                                    <tr v-if="transactionStore.transactions.data.length === 0">
                                        <td colspan="12" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data transaksi yang ditemukan.</p>
                                                <button @click="transactionStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(transaction, index) in transactionStore.transactions.data"
                                        :key="transaction.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (transactionStore.transactions.current_page - 1) *
                                                transactionStore.transactions.per_page }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ transaction.invoice_number }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ transaction.customer?.name ?? 'Guest' }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ transaction.transaction_date }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ transaction.user?.name ?? 'N/A' }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(transaction.total) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(transaction.discount) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(transaction.subtotal) }}
                                        </td>
                                       <td class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    {{ formatNumber(transaction.subtotal * (transaction.tax / 100)) }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Pajak {{ formatNumber(transaction.tax) }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(transaction.total_amount) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1">
                                                <div class="flex gap-2">
                                                    <BadgeStatusTransaction
                                                        :type="transaction.status"
                                                        class="text-xs font-medium rounded px-2 py-0.5"
                                                    />
                                                    <BadgeTypeSourceTransaction
                                                        :type="transaction.source_transaction"
                                                        class="text-xs font-medium rounded px-2 py-0.5"
                                                    />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link
                                                    :href="route('admin.transaksi.transaction.show', transaction.slug)"
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
                            <Pagination :links="transactionStore.transactions.links"
                                :current-page="transactionStore.transactions.current_page"
                                :per-page="transactionStore.transactions.per_page"
                                :total="transactionStore.transactions.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
