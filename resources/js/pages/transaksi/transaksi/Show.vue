<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/helpers/currencyHelper';
import {
    Package,
    ArrowRight,
    Clipboard,
    Calendar,
    User,
    FileText,
    CreditCard,
    Receipt,
    ChevronDown,
    Wallet,
    Tag,
    TrendingUp,
    CheckCircle,
    Clock
} from 'lucide-vue-next';
import { Transaction } from '@/types/transaction';
import BadgeStatusTransaction from '@/components/BadgeStatusTransaction.vue';
import BadgeTypeSourceTransaction from '@/components/BadgeTypeSourceTransaction.vue';
import { useToast } from 'vue-toastification';

const props = defineProps<{
    title: string;
    desc: string;
    transaction: Transaction;
}>();

const toast = useToast();
const showProductsSection = ref(true);
const showPaymentsSection = ref(true);
const isHeaderVisible = ref(false);
const activeTab = ref('details');

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    setTimeout(() => {
        isHeaderVisible.value = true;
    }, 100);
});

const handleScroll = () => {
    // Add scroll-based effects if needed
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    toast.info(`Copied: ${text}`, {
        timeout: 2000,
        closeOnClick: true,
        pauseOnHover: true,
        draggable: true,
    });
};

const toggleSection = (section: string) => {
    if (section === 'products') {
        showProductsSection.value = !showProductsSection.value;
    } else if (section === 'payments') {
        showPaymentsSection.value = !showPaymentsSection.value;
    }
};

const totalPaid = computed(() => {
    if (!props.transaction.payments || props.transaction.payments.length === 0) return 0;
    return props.transaction.payments.reduce((sum, payment) => sum + payment.amount, 0);
});

const remainingAmount = computed(() => {
    return props.transaction.total_amount - totalPaid.value;
});

const isPaid = computed(() => {
    return props.transaction.status === 'PAID' || remainingAmount.value <= 0;
});

const paymentStatus = computed(() => {
    if (isPaid.value) {
        return {
            text: 'PAID',
            icon: CheckCircle,
            color: 'bg-green-500 dark:bg-green-600'
        };
    } else {
        return {
            text: 'PENDING',
            icon: Clock,
            color: 'bg-amber-500 dark:bg-amber-600'
        };
    }
});

const formattedDate = computed(() => {
    const date = new Date(props.transaction.transaction_date);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
});
</script>

<template>
    <AppLayout>

        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header with animation -->
                <div class="mb-8 transform transition-all duration-500"
                    :class="isHeaderVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
                    <div class="flex flex-col md:flex-row md:items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white leading-tight">
                                {{ title }}
                            </h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                {{ desc }}
                            </p>
                        </div>

                        <div class="mt-4 md:mt-0">
                            <Link :href="route('admin.transaksi.transaction.index')"
                                class="group relative inline-flex items-center justify-center px-6 py-2.5 rounded-lg
                                text-sm font-medium text-white bg-primary-600 hover:bg-primary-700
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500
                                dark:bg-primary-600 dark:hover:bg-primary-700 transition-all duration-300 overflow-hidden">
                            <span
                                class="absolute right-0 translate-x-full group-hover:translate-x-0 transition-transform duration-300">
                                <ArrowRight class="h-5 w-5" />
                            </span>
                            <span
                                class="pr-2 group-hover:translate-x-[-8px] transition-transform duration-300">Kembali</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden mb-8 transform transition-all duration-500 hover:shadow-xl"
                    :class="isHeaderVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                    style="transition-delay: 100ms;">
                    <div class="bg-gradient-to-r from-primary-500 to-primary-600 p-6">
                        <div class="flex flex-col md:flex-row items-center justify-between">
                            <div class="flex items-center">
                                <div :class="`rounded-full p-3 ${paymentStatus.color} bg-opacity-90 mr-4`">
                                    <component :is="paymentStatus.icon" class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-white mb-1">Invoice #{{
                                        transaction.invoice_number }}</h2>
                                    <div class="flex space-x-2">
                                        <BadgeStatusTransaction :type="transaction.status"
                                            class="text-xs font-medium rounded-full px-2.5 py-1" />
                                        <BadgeTypeSourceTransaction :type="transaction.source_transaction"
                                            class="text-xs font-medium rounded-full px-2.5 py-1" />
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 md:mt-0 text-right">
                                <div class="text-sm text-white text-opacity-80">Total Amount</div>
                                <div class="text-2xl font-bold text-white">{{ formatNumber(transaction.total_amount) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="mb-8 transform transition-all duration-500"
                    :class="isHeaderVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                    style="transition-delay: 200ms;">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-8" aria-label="Tabs">
                            <button @click="activeTab = 'details'" :class="[
                                activeTab === 'details'
                                    ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200'
                            ]">
                                <span class="flex items-center">
                                    <Receipt class="h-5 w-5 mr-2" />
                                    Detail Transaksi
                                </span>
                            </button>

                            <button @click="activeTab = 'products'" :class="[
                                activeTab === 'products'
                                    ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200'
                            ]">
                                <span class="flex items-center">
                                    <Package class="h-5 w-5 mr-2" />
                                    Produk
                                </span>
                            </button>

                            <button v-if="transaction.payments && transaction.payments.length > 0"
                                @click="activeTab = 'payments'" :class="[
                                    activeTab === 'payments'
                                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200'
                                ]">
                                <span class="flex items-center">
                                    <CreditCard class="h-5 w-5 mr-2" />
                                    Pembayaran
                                </span>
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="transform transition-all duration-500"
                    :class="isHeaderVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'"
                    style="transition-delay: 300ms;">

                    <!-- Transaction Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-8">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row gap-8">
                                    <!-- Left Side -->
                                    <div class="w-full md:w-2/3">
                                        <h2
                                            class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                                            <FileText class="h-5 w-5 mr-2 text-primary-500" />
                                            Informasi Transaksi
                                        </h2>

                                        <!-- Info Cards Grid -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <!-- Invoice Card -->
                                            <div
                                                class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 transform transition-all hover:scale-[1.02] hover:shadow-md duration-300">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div
                                                            class="bg-primary-100 dark:bg-primary-900 p-2 rounded-lg mr-3">
                                                            <Receipt
                                                                class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                                                        </div>
                                                        <span
                                                            class="text-sm font-medium text-gray-700 dark:text-gray-300">No.
                                                            Invoice</span>
                                                    </div>
                                                    <button @click="copyToClipboard(transaction.invoice_number)"
                                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full p-1 transition-colors">
                                                        <Clipboard class="h-4 w-4" />
                                                    </button>
                                                </div>
                                                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{
                                                    transaction.invoice_number }}</p>
                                            </div>

                                            <!-- Date Card -->
                                            <div
                                                class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 transform transition-all hover:scale-[1.02] hover:shadow-md duration-300">
                                                <div class="flex items-center">
                                                    <div class="bg-amber-100 dark:bg-amber-900 p-2 rounded-lg mr-3">
                                                        <Calendar class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                                                        Transaksi</span>
                                                </div>
                                                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">{{
                                                    formattedDate }}</p>
                                            </div>

                                            <!-- Customer Card -->
                                            <div
                                                class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 transform transition-all hover:scale-[1.02] hover:shadow-md duration-300">
                                                <div class="flex items-center">
                                                    <div class="bg-green-100 dark:bg-green-900 p-2 rounded-lg mr-3">
                                                        <User class="h-5 w-5 text-green-600 dark:text-green-400" />
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan</span>
                                                </div>
                                                <p v-if="transaction.customer"
                                                    class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ transaction.customer.name }}
                                                </p>
                                                <p v-else
                                                    class="mt-2 text-lg font-semibold text-gray-400 dark:text-gray-500">
                                                    Umum</p>
                                            </div>

                                            <!-- Cashier Card -->
                                            <div
                                                class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5 transform transition-all hover:scale-[1.02] hover:shadow-md duration-300">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 dark:bg-blue-900 p-2 rounded-lg mr-3">
                                                        <User class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                                    </div>
                                                    <span
                                                        class="text-sm font-medium text-gray-700 dark:text-gray-300">Kasir</span>
                                                </div>
                                                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">
                                                    {{ transaction.user ? transaction.user.name : '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Notes Card -->
                                        <div
                                            class="mt-5 bg-gray-50 dark:bg-gray-700 rounded-xl p-5 transform transition-all hover:scale-[1.02] hover:shadow-md duration-300">
                                            <div class="flex items-center">
                                                <div class="bg-purple-100 dark:bg-purple-900 p-2 rounded-lg mr-3">
                                                    <FileText class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                                </div>
                                                <span
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</span>
                                            </div>
                                            <p class="mt-2 text-base text-gray-900 dark:text-white">
                                                {{ transaction.notes || '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Right Side - Payment Summary -->
                                    <div class="w-full md:w-1/3">
                                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 sticky top-4">
                                            <h2
                                                class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                                <Wallet class="h-5 w-5 mr-2 text-primary-500" />
                                                Ringkasan Pembayaran
                                            </h2>

                                            <div class="space-y-4">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total
                                                        Transaksi</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        formatNumber(transaction.total) }}</span>
                                                </div>

                                                <div class="flex justify-between items-center">
                                                    <div class="flex items-center">
                                                        <Tag class="h-4 w-4 mr-1 text-red-500" />
                                                        <span
                                                            class="text-sm text-gray-600 dark:text-gray-400">Diskon</span>
                                                    </div>
                                                    <span class="text-sm font-medium text-red-600 dark:text-red-400">-
                                                        {{ formatNumber(transaction.discount) }}</span>
                                                </div>

                                                <div class="flex justify-between items-center">
                                                    <span
                                                        class="text-sm text-gray-600 dark:text-gray-400">Subtotal</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        formatNumber(transaction.subtotal) }}</span>
                                                </div>

                                                <div class="flex justify-between items-center">
                                                    <div class="flex items-center">
                                                        <TrendingUp class="h-4 w-4 mr-1 text-blue-500" />
                                                        <span class="text-sm text-gray-600 dark:text-gray-400">Pajak ({{
                                                            transaction.tax }}%)</span>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                        formatNumber(transaction.subtotal * (transaction.tax / 100))
                                                    }}</span>
                                                </div>

                                                <div class="mt-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                                                    <div class="flex justify-between items-center">
                                                        <span
                                                            class="text-base font-medium text-gray-900 dark:text-white">Total
                                                            Net</span>
                                                        <span
                                                            class="text-lg font-bold text-primary-600 dark:text-primary-400">{{
                                                                formatNumber(transaction.total_amount) }}</span>
                                                    </div>
                                                </div>

                                                <div class="mt-2 pt-4 border-t border-gray-200 dark:border-gray-600">
                                                    <div class="flex flex-col space-y-2">
                                                        <div class="flex justify-between items-center">
                                                            <span
                                                                class="text-sm text-gray-600 dark:text-gray-400">Dibayar</span>
                                                            <span
                                                                class="text-sm font-medium text-green-600 dark:text-green-400">{{
                                                                    formatNumber(transaction.paid_amount) }}</span>
                                                        </div>

                                                        <div class="flex justify-between items-center">
                                                            <span
                                                                class="text-sm text-gray-600 dark:text-gray-400">Kembalian</span>
                                                            <span
                                                                class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                                    formatNumber(transaction.change_amount) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div v-if="!isPaid"
                                                    class="mt-2 pt-4 border-t border-gray-200 dark:border-gray-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                                                    <div class="flex justify-between items-center">
                                                        <span
                                                            class="text-sm font-medium text-red-600 dark:text-red-400">Sisa
                                                            Pembayaran</span>
                                                        <span
                                                            class="text-sm font-bold text-red-600 dark:text-red-400">{{
                                                                formatNumber(remainingAmount) }}</span>
                                                    </div>
                                                </div>

                                                <div v-else
                                                    class="mt-2 pt-4 border-t border-gray-200 dark:border-gray-600 bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                                                    <div class="flex items-center justify-center">
                                                        <CheckCircle class="h-5 w-5 text-green-500 mr-2" />
                                                        <span
                                                            class="text-sm font-medium text-green-600 dark:text-green-400">Pembayaran
                                                            Lunas</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products Tab -->
                    <div v-if="activeTab === 'products'" class="space-y-8">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                        <Package class="h-5 w-5 mr-2 text-primary-500" />
                                        Detail Produk
                                    </h2>
                                    <button @click="toggleSection('products')"
                                        class="text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full p-2 transition-colors">
                                        <ChevronDown class="h-5 w-5"
                                            :class="showProductsSection ? '' : 'transform rotate-180'" />
                                    </button>
                                </div>

                                <div v-if="showProductsSection" class="transition-all duration-500"
                                    :class="{ 'max-h-0 opacity-0': !showProductsSection, 'max-h-[2000px] opacity-100': showProductsSection }">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead
                                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Produk
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Harga
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Qty
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Total
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Diskon
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Subtotal
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                <tr v-for="(detail, index) in transaction.details" :key="index"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                                    :class="{ 'animate-pulse-once': index < 3 }">
                                                    <td class="px-6 py-5 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                                <Package
                                                                    class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                            </div>
                                                            <div class="ml-4">
                                                                <div
                                                                    class="text-sm font-medium text-gray-900 dark:text-white">
                                                                    {{ detail.product ? detail.product.name : 'Produk tidak tersedia' }}
                                                                </div>
                                                                <div v-if="detail.product"
                                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                                    {{ detail.product.code }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                                        {{ formatNumber(detail.price) }}
                                                    </td>
                                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                                        <span
                                                            class="inline-flex flex-col items-center justify-center px-3 py-1 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-medium">
                                                            {{ detail.quantity }} {{
                                                            detail.product?.unitConversions?.[0]?.fromUnit?.name }}
                                                            <span class="text-xs text-blue-700 dark:text-blue-300">
                                                                ({{ detail.base_quantity }} {{
                                                                detail.product?.unitConversions?.[0]?.toUnit?.name }})
                                                            </span>
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                                        {{ formatNumber(detail.price * detail.quantity) }}
                                                    </td>
                                                    <td class="px-6 py-5 whitespace-nowrap text-center text-sm">
                                                        <span v-if="detail.discount > 0"
                                                            class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                            {{ formatNumber(detail.discount) }}
                                                        </span>
                                                        <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                                                    </td>
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ formatNumber(detail.subtotal) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot
                                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                                <tr>
                                                    <td colspan="5"
                                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Total
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-primary-600 dark:text-primary-400">
                                                        {{ formatNumber(transaction.subtotal) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div v-if="activeTab === 'payments' && transaction.payments && transaction.payments.length > 0"
                        class="space-y-8">
                        <div
                            class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-shadow duration-300">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                                        <CreditCard class="h-5 w-5 mr-2 text-primary-500" />
                                        Riwayat Pembayaran
                                    </h2>
                                    <button @click="toggleSection('payments')"
                                        class="text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full p-2 transition-colors">
                                        <ChevronDown class="h-5 w-5"
                                            :class="showPaymentsSection ? '' : 'transform rotate-180'" />
                                    </button>
                                </div>

                                <div v-if="showPaymentsSection" class="transition-all duration-500"
                                    :class="{ 'max-h-0 opacity-0': !showPaymentsSection, 'max-h-[2000px] opacity-100': showPaymentsSection }">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead
                                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                                <tr>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Tanggal
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Metode
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Referensi
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    <th scope="col"
                                                        class="px-6 py-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                        Jumlah
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody
                                                class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                <tr v-for="(payment, index) in transaction.payments" :key="index"
                                                    class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                                    :class="{ 'animate-fade-in': index < 3 }"
                                                    :style="{ animationDelay: index * 100 + 'ms' }">
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        {{ payment.payment_date }}
                                                    </td>
                                                    <td class="px-6 py-5 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div
                                                                class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                                <CreditCard
                                                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                                            </div>
                                                            <div
                                                                class="ml-3 text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ payment.payment_method }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                        <div class="flex items-center">
                                                            <div v-if="payment.payment_reference">
                                                                <span>{{ payment.payment_reference }}</span>
                                                                <button
                                                                    @click="copyToClipboard(payment.payment_reference)"
                                                                    class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                                                    <Clipboard class="h-4 w-4" />
                                                                </button>
                                                            </div>
                                                            <span v-else>-</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-5 whitespace-nowrap text-center">
                                                        <span
                                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                            :class="{
                                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': payment.status === 'PAID',
                                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': payment.status === 'PENDING',
                                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': payment.status === 'FAILED'
                                                            }">
                                                            {{ payment.status }}
                                                        </span>
                                                    </td>
                                                    <td
                                                        class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ formatNumber(payment.amount) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot
                                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                                                <tr>
                                                    <td colspan="4"
                                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        Total Pembayaran
                                                    </td>
                                                    <td
                                                        class="px-6 py-4 whitespace-nowrap text-right text-base font-bold text-primary-600 dark:text-primary-400">
                                                        {{ formatNumber(totalPaid) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile actions - Fixed at bottom with animation -->
                <div
                    class="sm:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 p-4 border-t border-gray-200 dark:border-gray-700 shadow-lg transform transition-transform duration-300">
                    <Link :href="route('admin.transaksi.transaction.index')" class="w-full inline-flex justify-center items-center rounded-lg
                          bg-primary-600 hover:bg-primary-700 px-4 py-3 text-sm font-medium text-white
                          shadow focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                          dark:bg-primary-600 dark:hover:bg-primary-700 transition-all duration-300">
                    <ArrowRight class="h-5 w-5 mr-2" />
                    Kembali ke Daftar Transaksi
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Animation utilities */
@keyframes pulse-once {
    0% {
        opacity: 0.6;
    }

    50% {
        opacity: 1;
    }

    100% {
        opacity: 1;
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-pulse-once {
    animation: pulse-once 1s ease-in-out;
}

.animate-fade-in {
    animation: fade-in 0.5s ease-out forwards;
}
</style>
