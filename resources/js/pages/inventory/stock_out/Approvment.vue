<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Package, Info, AlertTriangle, Truck, Calendar, User, FileText, CreditCard, Ban, CheckCircle, CalendarDays, XCircle, Clock, User2, ArrowLeftCircle } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { StockOut, StockOutApproval } from '@/types/stock_out';
import { formatDate } from '@/helpers/dateStringHelper';
import BadgeInventory from '@/components/BadgeInventory.vue';
import Badge from '@/components/Badge.vue';
import { StatusPoOption } from '@/types/status_po';
import { useToast } from 'vue-toastification';
import { useStockCardStore } from '@/stores/stockCardStore';
import { useStockOutStore } from '@/stores/stockOutStore';
import Swal from 'sweetalert2';
import VButtonSelect from '@/components/VButtonSelect.vue';
import BaseButton from '@/components/BaseButton.vue';

const props = defineProps<{
    title: string;
    desc: string;
    stock_out: StockOut;
    stock_out_approval: StockOutApproval;
    status: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const toast = useToast();
const stockOutStore = useStockOutStore();

const formApprovalStockOut = ref(<StockOutApproval>{
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


watch(() => formApprovalStockOut.value.status, (newStatusId) => {
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    if (!props.currentUser) return;

    if (newStatusId === 'RECEIVED') {
        formApprovalStockOut.value.user_ack_id = props.currentUser.id;
        formApprovalStockOut.value.ack_date = now;
        formApprovalStockOut.value.user_reject_id = null;
        formApprovalStockOut.value.reject_date = null;
    } else if (newStatusId === 'CANCELED') {
        formApprovalStockOut.value.user_reject_id = props.currentUser.id;
        formApprovalStockOut.value.reject_date = now;
        formApprovalStockOut.value.user_ack_id = null;
        formApprovalStockOut.value.ack_date = null;
    } else {
        formApprovalStockOut.value.user_ack_id = null;
        formApprovalStockOut.value.ack_date = null;
        formApprovalStockOut.value.user_reject_id = null;
        formApprovalStockOut.value.reject_date = null;
    }
});
async function approvalForm() {
    const result = await Swal.fire({
        title: 'Perhatian',
        html: 'Apakah data Pengeluaran Barang sudah sesuai?',
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
            await stockOutStore.approvalStockOut(
                props.stock_out_approval.slug,
                formApprovalStockOut.value
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
                                            <VButtonSelect v-model="formApprovalStockOut.status"
                                                :options="statusOptions"
                                                class="flex space-x-2 rounded-md overflow-hidden" />
                                        </div>

                                        <!-- Status Display dengan visualisasi yang lebih jelas -->
                                        <div v-if="formApprovalStockOut.status"
                                            class="space-y-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800 border border-green-300': formApprovalStockOut.status === 'RECEIVED',
                                                        'bg-red-100 text-red-800 border border-red-300': formApprovalStockOut.status === 'CANCELED',
                                                        'bg-blue-100 text-blue-800 border border-blue-300': formApprovalStockOut.status !== 'RECEIVED' && formApprovalStockOut.status !== 'CANCELED',
                                                    }">
                                                    <template v-if="formApprovalStockOut.status === 'RECEIVED'">
                                                        <CheckCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template v-else-if="formApprovalStockOut.status === 'CANCELED'">
                                                        <XCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template v-else>
                                                        <Clock class="w-4 h-4 mr-1" />
                                                    </template>
                                                    Status: {{ formApprovalStockOut.status }}
                                                </span>
                                            </div>

                                            <!-- Approval Information -->
                                            <div v-if="formApprovalStockOut.user_ack_id && formApprovalStockOut.ack_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                    <Calendar class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                </div>
                                                <div class="text-sm text-green-700 dark:text-green-400">
                                                    <div><span class="font-medium">Disetujui</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formApprovalStockOut.ack_date }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rejection Information -->
                                            <div v-if="formApprovalStockOut.user_reject_id && formApprovalStockOut.reject_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                    <Calendar class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                </div>
                                                <div class="text-sm text-red-700 dark:text-red-400">
                                                    <div><span class="font-medium">Dibatalkan</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formApprovalStockOut.reject_date }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Default Message yang muncul ketika belum ada pilihan -->
                                        <div v-if="!formApprovalStockOut.status"
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
                                            <Link :href="route('admin.inventory.stock_out.index')" class="inline-flex items-center justify-center rounded-md border border-gray-300
                                            bg-white px-4 py-2 text-sm font-medium text-gray-700
                                            shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                            focus:ring-primary-500 focus:ring-offset-2
                                            dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                            dark:hover:bg-gray-600 transition-colors">
                                            <ArrowLeftCircle class="w-4 h-4 mr-2" />
                                            Kembali
                                            </Link>

                                            <BaseButton :loading="stockOutStore.isLoading"
                                                :text="formApprovalStockOut.status === 'RECEIVED' ? 'Setujui' : formApprovalStockOut.status === 'CANCELED' ? 'REJECTED' : 'APPROVED'"
                                                loadingText="Proses Approval..."
                                                :color="formApprovalStockOut.status === 'RECEIVED' ? 'emerald' : formApprovalStockOut.status === 'CANCELED' ? 'red' : 'blue'"
                                                :disabled="!formApprovalStockOut.status" class="flex items-center">
                                                <template #icon>
                                                    <template v-if="formApprovalStockOut.status === 'PROSESS'">
                                                        <CheckCircle class="w-4 h-4 mr-2" />
                                                    </template>
                                                    <template v-else-if="formApprovalStockOut.status === 'PROSESS'">
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
