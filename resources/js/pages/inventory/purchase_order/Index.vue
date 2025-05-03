<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Badge from '@/components/Badge.vue';
import Pagination from '@/components/Pagination.vue';
import { Eye, Pencil, Trash2, FileX, FileText, CircleCheckBig } from 'lucide-vue-next';
import { PurchaseOrderPageProps } from '@/types/purchase_order';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import { formatNumber } from '@/helpers/currencyHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';

const props = defineProps<PurchaseOrderPageProps>();
const purchaseOrderStore = usePurchaseOrderStore();

onMounted(() => {
    purchaseOrderStore.initializeFromProps(props);
});

watch(() => props.purchase_orders, (newPurchaseOrders) => {
    purchaseOrderStore.purchase_orders = newPurchaseOrders;
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
                            <Link :href="route('admin.inventory.purchase_order.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <FileText class="w-4 h-4 mr-2" />
                            Tambah PO
                            </Link>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="purchaseOrderStore.searchQuery"
                                    placeholder="Cari berdasarkan nomor PO atau supplier..."
                                    @update:modelValue="purchaseOrderStore.setSearchQuery" />
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div v-if="purchaseOrderStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data purchase order...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            No.PO</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Supplier</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status</th>
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
                                    <tr v-if="purchaseOrderStore.purchase_orders.data.length === 0">
                                        <td colspan="11" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data purchase order yang ditemukan.</p>
                                                <button @click="purchaseOrderStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(purchase_order, index) in purchaseOrderStore.purchase_orders.data"
                                        :key="purchase_order.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (purchaseOrderStore.purchase_orders.current_page - 1) *
                                                purchaseOrderStore.purchase_orders.per_page }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                            {{ purchase_order.po_number }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="font-medium">{{ purchase_order.supplier?.name || '—' }}</div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ purchase_order.po_date }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Est: {{ purchase_order.expected_date }}
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col gap-1">
                                                <Badge :type="purchase_order.is_active ? 'success' : 'danger'"
                                                    :text="purchase_order.is_active ? 'Aktif' : 'Nonaktif'" />
                                                <BadgeInventory :type="purchase_order.status" />
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(purchase_order.subtotal) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(purchase_order.discount) }}
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-700 dark:text-gray-200">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ formatNumber((purchase_order.subtotal -
                                                    purchase_order.discount) *
                                                    (purchase_order.tax / 100)) }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ formatNumber(purchase_order.tax) }} %
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right text-gray-700 dark:text-gray-200">
                                            {{ formatNumber(purchase_order.total_net) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link
                                                    :href="route('admin.inventory.purchase_order.show', purchase_order.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-purple-600 hover:text-white hover:bg-purple-600 dark:text-purple-400 dark:hover:text-white dark:hover:bg-purple-500 transition-colors duration-150">
                                                <Eye class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="purchase_order.status === 'PROSESS'"
                                                    :href="route('admin.inventory.purchase_order.edit', purchase_order.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-indigo-600 hover:text-white hover:bg-indigo-600 dark:text-indigo-400 dark:hover:text-white dark:hover:bg-indigo-500 transition-colors duration-150">
                                                <Pencil class="w-4 h-4" />
                                                </Link>

                                                <Link v-if="purchase_order.status === 'PROSESS'"
                                                    :href="route('admin.inventory.purchase_order.approval.view', purchase_order.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-green-600 hover:text-white hover:bg-green-600 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-500 transition-colors duration-150">
                                                <CircleCheckBig class="w-4 h-4" />
                                                </Link>

                                                <button v-if="purchase_order.status === 'PROSESS'" @click="purchaseOrderStore.deletePurchaseOrder(purchase_order)"
                                                    :disabled="purchaseOrderStore.isLoading"
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
                            <Pagination :links="purchaseOrderStore.purchase_orders.links"
                                :current-page="purchaseOrderStore.purchase_orders.current_page"
                                :per-page="purchaseOrderStore.purchase_orders.per_page"
                                :total="purchaseOrderStore.purchase_orders.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
