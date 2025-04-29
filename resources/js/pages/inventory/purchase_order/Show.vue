<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import { Supplier } from '@/types/supplier';
import { Product } from '@/types/produk';
import { Unit } from '@/types/unit';
import { UnitConversion } from '@/types/unit_conversion';
import { StatusPoOption } from '@/types/status_po';
import { PurchaseOrder, PurchaseOrderDetail } from '@/types/purchase_order';
import BadgeInventory from '@/components/BadgeInventory.vue';
import Badge from '@/components/Badge.vue';
import { formatCurrency } from '@/helpers/currencyHelper';
import { formatDate } from '@/helpers/dateStringHelper';

const props = defineProps<{
    title: string;
    desc: string;
    purchase_order: PurchaseOrder;
}>();

const receiveProgress = computed(() => {
    const { po_date, expected_date, status } = props.purchase_order as {
        po_date: string;
        expected_date: string;
        status: string;
    };

    if (!po_date || !expected_date) return 0;

    const poDate = new Date(po_date + 'T00:00:00');
    const expectedDate = new Date(expected_date + 'T00:00:00');
    const currentDate = new Date();

    if (isNaN(poDate.getTime()) || isNaN(expectedDate.getTime())) return 0;

    if (status === 'RECEIVED') return 100;
    if (status === 'CANCELED') return 0;

    const totalDays = (expectedDate.getTime() - poDate.getTime()) / 86400000;

    const passedDays = (currentDate.getTime() - poDate.getTime()) / 86400000;

    if (passedDays <= 0) return 0;
    if (passedDays >= totalDays) return 100;

    return Math.round((passedDays / totalDays) * 100);
});
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
                                    <Link v-if="purchase_order.status === 'PROSESS'" :href="route('admin.inventory.purchase_order.edit', purchase_order.slug)"
                                        class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                                        bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                        shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                                        focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Purchase Order
                                    </Link>
                                    <Link :href="route('admin.inventory.purchase_order.index')" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300
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

                        <!-- Status Badge -->
                        <div class="mb-6">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                <div class="flex items-center space-x-2">
                                    <Badge :type="purchase_order.is_active ? 'success' : 'danger'"
                                        :text="purchase_order.is_active ? 'Aktif' : 'Nonaktif'" />

                                    <BadgeInventory :type="purchase_order.status" />
                                </div>

                                <div class="text-sm text-gray-600 dark:text-gray-300 text-right sm:text-left">
                                    <span class="font-medium">Dibuat pada:</span> {{
                                    formatDate(purchase_order.created_at) }}
                                </div>
                            </div>


                            <!-- Progress bar for received items -->
                            <div v-if="['RECEIVED', 'PROSESS', 'CANCELED'].includes(purchase_order.status)"
                                class="mt-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Received
                                        Progress</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                        receiveProgress }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full"
                                        :style="{ width: `${receiveProgress}%` }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Order Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Purchase Order
                                    Details</h2>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">PO Number:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            purchase_order.po_number }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">PO Date:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            formatDate(purchase_order.po_date) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Expected Delivery:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            formatDate(purchase_order.expected_date) }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="purchase_order.ack_date">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Acknowledgment
                                            Date:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            formatDate(purchase_order.ack_date) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Supplier Information
                                </h2>
                                <div class="space-y-2" v-if="purchase_order.supplier">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Supplier Name:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            purchase_order.supplier.name }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="purchase_order.supplier.name">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Contact Person:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            purchase_order.supplier.name }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="purchase_order.supplier.phone">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Phone:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            purchase_order.supplier.phone }}</span>
                                    </div>
                                    <div class="flex justify-between" v-if="purchase_order.supplier.email">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Email:</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            purchase_order.supplier.email }}</span>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                    No supplier information available
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Order Items -->
                        <div class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Purchase Order Items</h2>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                No
                                            </th>
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Produk
                                            </th>
                                            <th scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Jumlah
                                            </th>
                                            <th scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Jumlah Konversi / Konversi
                                            </th>
                                            <th scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Harga
                                            </th>
                                            <th scope="col"
                                                class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(detail, index) in purchase_order.details" :key="detail.id">
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ index + 1 }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ detail.product?.name || 'N/A' }}
                                                <span v-if="detail.product?.code"
                                                    class="block text-xs text-gray-500 dark:text-gray-400">
                                                    {{ detail.product.code }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ detail.quantity || 'N/A' }}
                                                <span v-if="detail.product?.unitConversions[0]?.fromUnit?.name"
                                                    class="block text-xs text-gray-500 dark:text-gray-400">
                                                    {{ detail.product?.unitConversions[0]?.fromUnit?.name }}
                                                </span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <span v-if="detail.received_base_quantity">
                                                    {{ detail.received_base_quantity }}
                                                    <span v-if="detail.product?.unitConversions[0]?.conversion_factor">
                                                        / {{ detail.product.unitConversions[0].conversion_factor }}
                                                    </span>
                                                </span>
                                                <span v-else>
                                                    N/A
                                                </span>
                                                <span v-if="detail.product?.unitConversions[0]?.toUnit?.name"
                                                    class="block text-xs text-gray-500 dark:text-gray-400">
                                                    {{ detail.product.unitConversions[0].toUnit.name }}
                                                </span>
                                            </td>

                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                                {{ formatCurrency(detail.price) }}
                                            </td>
                                            <td
                                                class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                                {{ formatCurrency(detail.subtotal) }}
                                            </td>
                                        </tr>
                                        <tr v-if="!purchase_order.details || purchase_order.details.length === 0">
                                            <td colspan="6"
                                                class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                                                No items in this purchase order
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                Total:
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(purchase_order.subtotal) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                Diskon:
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(purchase_order.discount) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                Subtotal:
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(purchase_order.subtotal - purchase_order.discount) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                Pajak ({{ purchase_order.tax }}%):
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency((purchase_order.subtotal - purchase_order.discount) *
                                                    (purchase_order.tax / 100)) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"
                                                class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                                Total Net:
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                                                {{ formatCurrency(purchase_order.total_net) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Notes and Additional Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Notes</h2>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 min-h-24">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                        {{ purchase_order.notes || 'No notes provided' }}
                                    </p>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-3">User Information</h2>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Pembuat PO:</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                purchase_order.user?.name || 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between" v-if="purchase_order.userAck">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Approver:</span>
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                purchase_order.userAck.name }}</span>
                                        </div>
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
