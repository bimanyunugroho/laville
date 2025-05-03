<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Package, Info, AlertTriangle, CheckCircle, XCircle, Clock, ArrowLeftCircle, User2, Calendar, } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { GoodReceipt, GoodReceiptApproval } from '@/types/good_receipt';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { useGoodReceiptStore } from '@/stores/goodReceiptStore';
import { StatusPoOption } from '@/types/status_po';
import VButtonSelect from '@/components/VButtonSelect.vue';
import { formatDate } from '@/helpers/dateStringHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';
import BaseButton from '@/components/BaseButton.vue';

const props = defineProps<{
    title: string;
    desc: string;
    good_receipt: GoodReceipt;
    good_receipt_approval: GoodReceiptApproval;
    status: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const toast = useToast();
const goodReceiptStore = useGoodReceiptStore();

const formApprovalGoodReceipt = ref(<GoodReceiptApproval>{
    user_ack_id: 0 || null,
    ack_date: null,
    user_reject_id: 0 || null,
    reject_date: null,
    status_receipt: null
});

const statusOptions = computed(() => {
    return props.status
        ?.filter(status => status.label !== 'PROSESS')
        .map(status => ({
            id: status.label,
            name: status.name
        })) || [];
});


watch(() => formApprovalGoodReceipt.value.status_receipt, (newStatusId) => {
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    if (!props.currentUser) return;

    if (newStatusId === 'RECEIVED') {
        formApprovalGoodReceipt.value.user_ack_id = props.currentUser.id;
        formApprovalGoodReceipt.value.ack_date = now;
        formApprovalGoodReceipt.value.user_reject_id = null;
        formApprovalGoodReceipt.value.reject_date = null;
    } else if (newStatusId === 'CANCELED') {
        formApprovalGoodReceipt.value.user_reject_id = props.currentUser.id;
        formApprovalGoodReceipt.value.reject_date = now;
        formApprovalGoodReceipt.value.user_ack_id = null;
        formApprovalGoodReceipt.value.ack_date = null;
    } else {
        formApprovalGoodReceipt.value.user_ack_id = null;
        formApprovalGoodReceipt.value.ack_date = null;
        formApprovalGoodReceipt.value.user_reject_id = null;
        formApprovalGoodReceipt.value.reject_date = null;
    }
});
async function approvalForm() {
    const result = await Swal.fire({
        title: 'Perhatian',
        html: 'Apakah data Penerimaan Barang sudah sesuai?',
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
            await goodReceiptStore.approvalGoodReceipt(
                props.good_receipt_approval.slug,
                formApprovalGoodReceipt.value
            );
            Swal.close();
        } catch (error) {
            Swal.close();
            toast.error(`Terjadi kesalahan: ${error}`);
        }
    }
}

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
                                                <BadgeInventory :type="good_receipt.status_receipt" />
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
                                            <div v-if="good_receipt.status_receipt === 'SELESAI'" class="mb-3">
                                                <span
                                                    class="block text-sm font-medium text-gray-500 dark:text-gray-400">Dikonfirmasi
                                                    Oleh</span>
                                                <span class="block text-base text-gray-900 dark:text-white">
                                                    {{ good_receipt.userAck?.name || '-' }}
                                                </span>
                                            </div>
                                            <div v-if="good_receipt.status_receipt === 'BATAL'" class="mb-3">
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
                        <div class="mt-8">
                            <form @submit.prevent="approvalForm">
                                <div class="">
                                    <div
                                        class="space-y-4 dark:bg-gray-700 bg-white p-5 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                                        <!-- Header -->
                                        <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                Form Persetujuan Penerimaan Barang
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
                                            <VButtonSelect v-model="formApprovalGoodReceipt.status_receipt"
                                                :options="statusOptions"
                                                class="flex space-x-2 rounded-md overflow-hidden" />
                                        </div>

                                        <!-- Status Display dengan visualisasi yang lebih jelas -->
                                        <div v-if="formApprovalGoodReceipt.status_receipt"
                                            class="space-y-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800 border border-green-300': formApprovalGoodReceipt.status_receipt === 'RECEIVED',
                                                        'bg-red-100 text-red-800 border border-red-300': formApprovalGoodReceipt.status_receipt === 'CANCELED',
                                                        'bg-blue-100 text-blue-800 border border-blue-300': formApprovalGoodReceipt.status_receipt !== 'RECEIVED' && formApprovalGoodReceipt.status_receipt !== 'CANCELED',
                                                    }">
                                                    <template v-if="formApprovalGoodReceipt.status_receipt === 'RECEIVED'">
                                                        <CheckCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template v-else-if="formApprovalGoodReceipt.status_receipt === 'CANCELED'">
                                                        <XCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template v-else>
                                                        <Clock class="w-4 h-4 mr-1" />
                                                    </template>
                                                    Status: {{ formApprovalGoodReceipt.status_receipt }}
                                                </span>
                                            </div>

                                            <!-- Approval Information -->
                                            <div v-if="formApprovalGoodReceipt.user_ack_id && formApprovalGoodReceipt.ack_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                    <Calendar class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                </div>
                                                <div class="text-sm text-green-700 dark:text-green-400">
                                                    <div><span class="font-medium">Disetujui</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formApprovalGoodReceipt.ack_date }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rejection Information -->
                                            <div v-if="formApprovalGoodReceipt.user_reject_id && formApprovalGoodReceipt.reject_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                    <Calendar class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                </div>
                                                <div class="text-sm text-red-700 dark:text-red-400">
                                                    <div><span class="font-medium">Dibatalkan</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formApprovalGoodReceipt.reject_date }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Default Message yang muncul ketika belum ada pilihan -->
                                        <div v-if="!formApprovalGoodReceipt.status_receipt"
                                            class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg my-4">
                                            <div class="flex items-start gap-3">
                                                <Info class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5" />
                                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                                    <p class="font-medium">Pilih tindakan approval</p>
                                                    <p class="mt-1">Silakan pilih status persetujuan untuk
                                                        melanjutkan
                                                        proses approval penerimaan barang</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons with enhanced styling -->
                                        <div
                                            class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600 mt-4">
                                            <Link :href="route('admin.inventory.good_receipt.index')" class="inline-flex items-center justify-center rounded-md border border-gray-300
                                            bg-white px-4 py-2 text-sm font-medium text-gray-700
                                            shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                            focus:ring-primary-500 focus:ring-offset-2
                                            dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                            dark:hover:bg-gray-600 transition-colors">
                                            <ArrowLeftCircle class="w-4 h-4 mr-2" />
                                            Kembali
                                            </Link>

                                            <BaseButton :loading="goodReceiptStore.isLoading"
                                                :text="formApprovalGoodReceipt.status_receipt === 'RECEIVED' ? 'Setujui' : formApprovalGoodReceipt.status_receipt === 'CANCELED' ? 'REJECTED' : 'APPROVED'"
                                                loadingText="Proses Approval..."
                                                :color="formApprovalGoodReceipt.status_receipt === 'RECEIVED' ? 'emerald' : formApprovalGoodReceipt.status_receipt === 'CANCELED' ? 'red' : 'blue'"
                                                :disabled="!formApprovalGoodReceipt.status_receipt" class="flex items-center">
                                                <template #icon>
                                                    <template v-if="formApprovalGoodReceipt.status_receipt === 'PROSESS'">
                                                        <CheckCircle class="w-4 h-4 mr-2" />
                                                    </template>
                                                    <template v-else-if="formApprovalGoodReceipt.status_receipt === 'PROSESS'">
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
    </AppLayout>
</template>
