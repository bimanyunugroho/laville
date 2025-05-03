<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { PurchaseOrder, PurchaseOrderApproval } from '@/types/purchase_order';
import { StatusPoOption } from '@/types/status_po';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import VButtonSelect from '@/components/VButtonSelect.vue';
import BaseButton from '@/components/BaseButton.vue';
import { User2, Calendar, User, ArrowLeftCircle, Info, CheckCircle, XCircle, Clock } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/helpers/dateStringHelper';
import { formatCurrency } from '@/helpers/currencyHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';
import Badge from '@/components/Badge.vue';

const props = defineProps<{
    title: string;
    desc: string;
    purchase_order: PurchaseOrder;
    purchase_order_approval: PurchaseOrderApproval;
    status: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const toast = useToast();
const purchaseOrderStore = usePurchaseOrderStore();

const formApprovalPurchaseOrder = ref(<PurchaseOrderApproval>{
    user_ack_id: 0 || null,
    ack_date: null,
    user_reject_id: 0 || null,
    reject_date: null,
    status: null
});

const statusOptions = computed(() => {
    return props.status
        ?.filter(status => status.label !== 'PROSESS')
        .map(status => ({
            id: status.label,
            name: status.name
        })) || [];
});


watch(() => formApprovalPurchaseOrder.value.status, (newStatusId) => {
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    if (!props.currentUser) return;

    if (newStatusId === 'RECEIVED') {
        formApprovalPurchaseOrder.value.user_ack_id = props.currentUser.id;
        formApprovalPurchaseOrder.value.ack_date = now;
        formApprovalPurchaseOrder.value.user_reject_id = null;
        formApprovalPurchaseOrder.value.reject_date = null;
    } else if (newStatusId === 'CANCELED') {
        formApprovalPurchaseOrder.value.user_reject_id = props.currentUser.id;
        formApprovalPurchaseOrder.value.reject_date = now;
        formApprovalPurchaseOrder.value.user_ack_id = null;
        formApprovalPurchaseOrder.value.ack_date = null;
    } else {
        formApprovalPurchaseOrder.value.user_ack_id = null;
        formApprovalPurchaseOrder.value.ack_date = null;
        formApprovalPurchaseOrder.value.user_reject_id = null;
        formApprovalPurchaseOrder.value.reject_date = null;
    }
});
async function approvalForm() {
    const result = await Swal.fire({
        title: 'Perhatian',
        html: 'Apakah data Purchase Order sudah sesuai?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal',
    });

    if (result.isConfirmed) {
        try {
            const loadingSwal = Swal.fire({
                title: 'Sedang Memproses Data...',
                html: 'Harap tunggu sebentar.',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
            });
            Swal.showLoading();
            await new Promise(resolve => setTimeout(resolve, 3000));
            await purchaseOrderStore.approvalPurchaseOrder(
                props.purchase_order_approval.slug,
                formApprovalPurchaseOrder.value
            );
            Swal.close();
        } catch (error) {
            Swal.close();
            toast.error(`Terjadi kesalahan: ${error}`);
        }
    }
}


const receiveProgress = computed(() => {
    const { po_date, expected_date, status } = props.purchase_order as {
        po_date: string;
        expected_date: string;
        status: string;
    };

    if (!po_date || !expected_date) return 0;

    const poDate = new Date(po_date);
    const expectedDate = new Date(expected_date);
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
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Remaining
                                        Days</span>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ receiveProgress }}%
                                    </span>
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

                                <div class="bg-gray-50 mt-4 dark:bg-gray-700 rounded-lg p-4">
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
                            <div>
                                <form @submit.prevent="approvalForm">
                                    <div class="">
                                        <div
                                            class="space-y-4 dark:bg-gray-700 bg-white p-5 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                                            <!-- Header -->
                                            <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-4">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    Form Persetujuan Purchase Order
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                    Silakan pilih status persetujuan untuk dokumen ini
                                                </p>
                                            </div>

                                            <!-- Status Selection dengan tampilan yang lebih menarik -->
                                            <div class="mb-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                    Status Persetujuan:
                                                </label>
                                                <VButtonSelect v-model="formApprovalPurchaseOrder.status"
                                                    :options="statusOptions"
                                                    class="flex space-x-2 rounded-md overflow-hidden" />
                                            </div>

                                            <!-- Status Display dengan visualisasi yang lebih jelas -->
                                            <div v-if="formApprovalPurchaseOrder.status"
                                                class="space-y-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
                                                        :class="{
                                                            'bg-green-100 text-green-800 border border-green-300': formApprovalPurchaseOrder.status === 'RECEIVED',
                                                            'bg-red-100 text-red-800 border border-red-300': formApprovalPurchaseOrder.status === 'CANCELED',
                                                            'bg-blue-100 text-blue-800 border border-blue-300': formApprovalPurchaseOrder.status !== 'RECEIVED' && formApprovalPurchaseOrder.status !== 'CANCELED',
                                                        }">
                                                        <template
                                                            v-if="formApprovalPurchaseOrder.status === 'RECEIVED'">
                                                            <CheckCircle class="w-4 h-4 mr-1" />
                                                        </template>
                                                        <template
                                                            v-else-if="formApprovalPurchaseOrder.status === 'CANCELED'">
                                                            <XCircle class="w-4 h-4 mr-1" />
                                                        </template>
                                                        <template v-else>
                                                            <Clock class="w-4 h-4 mr-1" />
                                                        </template>
                                                        Status: {{ formApprovalPurchaseOrder.status }}
                                                    </span>
                                                </div>

                                                <!-- Approval Information -->
                                                <div v-if="formApprovalPurchaseOrder.user_ack_id && formApprovalPurchaseOrder.ack_date"
                                                    class="flex items-center gap-3 p-4 rounded-lg border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 w-fit">
                                                    <div class="flex flex-col items-center justify-center gap-2">
                                                        <User2 class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                        <Calendar class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                    </div>
                                                    <div class="text-sm text-green-700 dark:text-green-400">
                                                        <div><span class="font-medium">Disetujui</span> oleh <strong>{{
                                                            props.currentUser?.name }}</strong></div>
                                                        <div class="italic">{{ formApprovalPurchaseOrder.ack_date }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Rejection Information -->
                                                <div v-if="formApprovalPurchaseOrder.user_reject_id && formApprovalPurchaseOrder.reject_date"
                                                    class="flex items-center gap-3 p-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 w-fit">
                                                    <div class="flex flex-col items-center justify-center gap-2">
                                                        <User2 class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                        <Calendar class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                    </div>
                                                    <div class="text-sm text-red-700 dark:text-red-400">
                                                        <div><span class="font-medium">Dibatalkan</span> oleh <strong>{{
                                                            props.currentUser?.name }}</strong></div>
                                                        <div class="italic">{{ formApprovalPurchaseOrder.reject_date }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Default Message yang muncul ketika belum ada pilihan -->
                                            <div v-if="!formApprovalPurchaseOrder.status"
                                                class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg my-4">
                                                <div class="flex items-start gap-3">
                                                    <Info class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5" />
                                                    <div class="text-sm text-blue-700 dark:text-blue-300">
                                                        <p class="font-medium">Pilih tindakan approval</p>
                                                        <p class="mt-1">Silakan pilih status persetujuan untuk
                                                            melanjutkan
                                                            proses approval purchase order</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Buttons with enhanced styling -->
                                            <div
                                                class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600 mt-4">
                                                <Link :href="route('admin.inventory.purchase_order.index')" class="inline-flex items-center justify-center rounded-md border border-gray-300
                                            bg-white px-4 py-2 text-sm font-medium text-gray-700
                                            shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                            focus:ring-primary-500 focus:ring-offset-2
                                            dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                            dark:hover:bg-gray-600 transition-colors">
                                                <ArrowLeftCircle class="w-4 h-4 mr-2" />
                                                Kembali
                                                </Link>

                                                <BaseButton :loading="purchaseOrderStore.isLoading"
                                                    :text="formApprovalPurchaseOrder.status === 'RECEIVED' ? 'Setujui' : formApprovalPurchaseOrder.status === 'CANCELED' ? 'REJECTED' : 'APPROVED'"
                                                    loadingText="Proses Approval..."
                                                    :color="formApprovalPurchaseOrder.status === 'RECEIVED' ? 'emerald' : formApprovalPurchaseOrder.status === 'CANCELED' ? 'red' : 'blue'"
                                                    :disabled="!formApprovalPurchaseOrder.status"
                                                    class="flex items-center">
                                                    <template #icon>
                                                        <template v-if="formApprovalPurchaseOrder.status === 'PROSESS'">
                                                            <CheckCircle class="w-4 h-4 mr-2" />
                                                        </template>
                                                        <template
                                                            v-else-if="formApprovalPurchaseOrder.status === 'PROSESS'">
                                                            <XCircle class="w-4 h-4 mr-2" />
                                                        </template>
                                                    </template>
                                                </BaseButton>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
