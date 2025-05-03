<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Supplier } from '@/types/supplier';
import { Product } from '@/types/produk';
import { Unit } from '@/types/unit';
import { UnitConversion } from '@/types/unit_conversion';
import { StatusPoOption } from '@/types/status_po';
import BaseButton from '@/components/BaseButton.vue';
import DateInput from '@/components/DateInput.vue';
import VSelect from '@/components/VSelect.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import TextInput from '@/components/TextInput.vue';
import { Calendar, Search, Package, Loader2, Percent, AlertTriangle, Save, Info } from 'lucide-vue-next';
import { formatCurrency } from '@/helpers/currencyHelper';
import TextNumber from '@/components/TextNumber.vue';
import { useGoodReceiptStore } from '@/stores/goodReceiptStore';
import { GoodReceipt, GoodReceiptDetail } from '@/types/good_receipt';

const props = defineProps<{
    title: string;
    desc: string;
    good_receipt: GoodReceipt;
    suppliers: Supplier[] | null;
    products: Product[];
    unit_conversions: UnitConversion[];
    units: Unit[] | any;
    status_receipts: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const goodReceiptStore = useGoodReceiptStore();
const tanggalPenerimaanBarang = props.good_receipt.receipt_date;
const isSubmitting = ref(false);
const hasModifieddetails = ref(false);

// Inisialisasi form dengan data dari props.good_receipt
const form = ref({
    receipt_number: props.good_receipt.receipt_number,
    purchase_order_id: props.good_receipt.purchase_order_id,
    supplier_id: props.good_receipt.supplier_id,
    user_id: props.good_receipt.user_id || props.currentUser?.id || 0,
    receipt_date: props.good_receipt.receipt_date,
    user_ack_id: props.good_receipt.user_ack_id,
    ack_date: props.good_receipt.ack_date,
    user_reject_id: props.good_receipt.user_reject_id,
    reject_date: props.good_receipt.reject_date,
    subtotal: props.good_receipt.subtotal,
    tax: props.good_receipt.tax,
    discount: props.good_receipt.discount,
    taxAmount: props.good_receipt.tax ? (props.good_receipt.subtotal - props.good_receipt.discount) * (props.good_receipt.tax / 100) : 0,
    total_net: props.good_receipt.total_net,
    status_receipt: props.good_receipt.status_receipt,
    notes: props.good_receipt.notes,
    is_active: props.good_receipt.is_active,
    supplier_name: props.good_receipt.supplier?.name || '',
    po_number: props.good_receipt.purchaseOrder?.po_number || '',
    details: (props.good_receipt.details ?? []).map(createExistDetail)
});

function createExistDetail(detail: GoodReceiptDetail): GoodReceiptDetail {
    return {
        id: detail.id || 0,
        purchase_order_detail_id: detail.purchase_order_detail_id || 0,
        product_id: detail.product_id || 0,
        unit_id: detail.unit_id || 0,
        quantity: detail.quantity || 0,
        base_quantity: detail.base_quantity || 0,
        price: detail.price || 0,
        subtotal: detail.subtotal || 0,
        received_quantity: detail.received_quantity || 0,
        received_base_quantity: detail.received_base_quantity || 0,
        product: detail.product || undefined,
        unit: detail.unit || undefined
    };
}

function calculateTotals() {
    form.value.subtotal = form.value.details.reduce((sum, detail) => sum + detail.subtotal, 0);
    const discountAmount = form.value.discount || 0;
    const subtotalWithDiscount = form.value.subtotal - discountAmount;
    const taxAmount = form.value.tax ? (subtotalWithDiscount * (form.value.tax / 100)) : 0;
    form.value.total_net = subtotalWithDiscount + taxAmount;
    form.value.taxAmount = taxAmount;
}

const updatedetailsubtotal = (index: number) => {
    const item = form.value.details[index];
    const price = item.price || 0;
    const qty = item.received_quantity || 0;

    item.subtotal = price * qty;
    calculateTotals();
    hasModifieddetails.value = true;
};

// Cari produk berdasarkan ID
function findProduct(productId: number | string): Product | undefined {
    return props.products?.find(product => product.id == productId);
}

// Cari unit berdasarkan ID
function findUnit(unitId: number | string): Unit | undefined {
    return props.units?.find((unit: Unit) => unit.id == unitId);
}

// Hitung base quantity berdasarkan unit conversion
function calculateBaseQuantity(detail: GoodReceiptDetail): { baseQuantity: number, received_base_quantity: number } {
    const product = findProduct(detail.product_id);
    const unit = findUnit(detail.unit_id);
    let baseQuantity = detail.quantity || 0;
    let receivedBaseQuantity = detail.received_quantity || 0;

    if (product && unit && props.unit_conversions) {
        const conversion = props.unit_conversions.find(
            uc => uc.product_id == product.id && uc.from_unit_id == unit.id
        );

        if (conversion) {
            baseQuantity = detail.quantity * conversion.conversion_factor;
            receivedBaseQuantity = detail.received_quantity * conversion.conversion_factor;
        }
    }

    return {
        baseQuantity,
        received_base_quantity: receivedBaseQuantity
    };
}

const handleQtyChange = (index: number) => {
    const item = form.value.details[index];
    updatedetailsubtotal(index);

    const { baseQuantity, received_base_quantity } = calculateBaseQuantity(item);
    item.base_quantity = baseQuantity;
    item.received_base_quantity = received_base_quantity;
};


const handleTaxDiscountChange = () => {
    calculateTotals();
};

const submitForm = () => {
    isSubmitting.value = true;
    calculateTotals();
    goodReceiptStore.editGoodReceipt(props.good_receipt.slug, form.value);
};

const statusReceiptOptions = computed(() => {
    return props.status_receipts?.map(status_receipt => ({
        id: status_receipt.label,
        name: status_receipt.name
    })) || [];
});

// Check if any details have exceeded their ordered quantity
const hasExceededdetails = computed(() => {
    return form.value.details.some(item => item.received_quantity > item.quantity);
});

// Track incomplete details
const incompletedetails = computed(() => {
    return form.value.details.filter(item => item.received_quantity < item.quantity).length;
});

// Auto-fill all receipt quantities to match order quantities
const fillAllQuantities = () => {
    form.value.details.forEach((item, index) => {
        item.received_quantity = item.quantity;
        updatedetailsubtotal(index);
    });
    hasModifieddetails.value = true;
};
</script>

<template>
    <AppLayout>

        <Head :title="title" />
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div
                            class="flex flex-col md:flex-row justify-between details-start md:details-center mb-6 gap-4">
                            <!-- Kiri: Title and Description -->
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>

                            <!-- Kanan: Info Box -->
                            <div
                                class="flex details-center space-x-3 bg-blue-50 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-100 dark:border-blue-800">
                                <div class="bg-blue-100 dark:bg-blue-800 rounded-lg p-2">
                                    <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Input Penerimaan
                                        Barang</div>
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ tanggalPenerimaanBarang }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Search Purchase Order Section -->
                        <div
                            class="mb-8 p-5 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex details-center">
                                <Info class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                                Data Nomor Purchase Order
                            </h2>

                            <!-- PO Details (if found) -->
                            <div v-if="form.purchase_order_id"
                                class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-green-100 dark:border-green-900">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Nomor PO</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ form.po_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ form.supplier_name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitForm">
                            <!-- Basic Information Section -->
                            <div
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 mb-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Dasar</h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <TextInput v-model="form.receipt_number" id="receipt-number"
                                        label="Nomor Penerimaan" readonly />

                                    <DateInput v-model="form.receipt_date" id="receipt-date"
                                        label="Tanggal Input Penerimaan" />

                                    <VSelect v-model="form.status_receipt" :options="statusReceiptOptions"
                                        option-value="id" option-label="name" label="Status" />

                                    <TextAreaInput v-model="form.notes" id="notes" label="Catatan"
                                        placeholder="Masukkan catatan (opsional)" />
                                </div>
                            </div>

                            <!-- details Section -->
                            <div v-if="form.details.length > 0"
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 mb-6">
                                <div class="flex justify-between details-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex details-center">
                                        <Package class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                                        Item Penerimaan
                                    </h3>

                                    <BaseButton text="Terima Semua Qty" @click="fillAllQuantities" color="green"
                                        size="sm" />
                                </div>

                                <!-- Warning if quantities exceeded -->
                                <div v-if="hasExceededdetails"
                                    class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 rounded-md border border-red-200 dark:border-red-800 flex details-center">
                                    <AlertTriangle class="w-5 h-5 mr-2" />
                                    <span>Ada barang yang kuantitas terimanya melebihi kuantitas pesan. Mohon periksa
                                        kembali.</span>
                                </div>

                                <!-- Info about incomplete details -->
                                <div v-if="incompletedetails > 0 && hasModifieddetails"
                                    class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-md border border-blue-200 dark:border-blue-800 flex details-center">
                                    <AlertTriangle class="w-5 h-5 mr-2" />
                                    <span>{{ incompletedetails }} barang belum diterima sepenuhnya.</span>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Produk
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Unit
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Qty Dipesan
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Qty Diterima
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Sisa
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Harga
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                                    Subtotal
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(item, index) in form.details" :key="index" :class="[
                                                item.received_quantity > item.quantity ? 'bg-red-50 dark:bg-red-900/30' : '',
                                                item.received_quantity < item.quantity ? 'bg-yellow-50 dark:bg-yellow-900/20' : '',
                                                item.received_quantity === item.quantity ? 'bg-green-50 dark:bg-green-900/20' : ''
                                            ]">
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    {{ item.product?.name }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    {{ item.unit?.name }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-medium">
                                                    {{ item.quantity }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    <TextNumber :id="`quantity-received-${index}`"
                                                        v-model="item.received_quantity"
                                                        @update:modelValue="handleQtyChange(index)" min="0"
                                                        class="w-24" />
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium" :class="[
                                                    item.quantity - item.received_quantity === 0 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'
                                                ]">
                                                    {{ item.quantity - item.received_quantity }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                    {{ formatCurrency(item.price) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ formatCurrency(item.price * item.received_quantity) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="6"
                                                    class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                                    Total
                                                </td>
                                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">
                                                    {{formatCurrency(form.details.reduce((sum, item) => sum +
                                                        (item.price * item.received_quantity), 0))}}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <!-- Totals Section -->
                            <div v-if="form.details.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <!-- Empty space or could add additional information here -->
                                </div>
                                <div class="md:col-span-1">
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                                        <!-- Subtotal -->
                                        <div class="flex justify-between details-center mb-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Subtotal:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">{{
                                                formatCurrency(form.subtotal) }}</span>
                                        </div>

                                        <!-- Diskon -->
                                        <div class="mb-3">
                                            <div class="flex justify-between details-center">
                                                <label for="discount" class="text-sm text-gray-600 dark:text-gray-300">
                                                    Diskon:
                                                </label>
                                                <div class="w-1/2">
                                                    <TextNumber id="discount" v-model.number="form.discount"
                                                        @update:modelValue="handleTaxDiscountChange"
                                                        class="text-right" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pajak (PPN) -->
                                        <div class="mb-3">
                                            <div class="flex justify-between details-center">
                                                <label for="tax" class="text-sm text-gray-600 dark:text-gray-300">
                                                    Pajak (PPN):
                                                </label>
                                                <div class="flex details-center w-1/2 space-x-1">
                                                    <TextNumber id="tax" v-model.number="form.tax"
                                                        @update:modelValue="handleTaxDiscountChange"
                                                        class="text-right" />
                                                    <Percent class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                </div>
                                            </div>
                                            <div class="flex justify-between details-center mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah
                                                    Pajak:</span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ formatCurrency(form.taxAmount) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Total -->
                                        <div
                                            class="flex justify-between details-center pt-3 mt-3 border-t border-gray-200 dark:border-gray-600">
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">Total
                                                Bersih:</span>
                                            <span class="font-bold text-lg text-blue-600 dark:text-blue-400">
                                                {{ formatCurrency(form.total_net) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div
                                class="flex details-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <Link :href="route('admin.inventory.good_receipt.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                         bg-white px-4 py-2 text-sm font-medium text-gray-700
                                         shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                         focus:ring-primary-500 focus:ring-offset-2
                                         dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                         dark:hover:bg-gray-600 transition-colors">
                                Batal
                                </Link>
                                <BaseButton
                                    :disabled="!form.purchase_order_id || isSubmitting || form.details.length === 0"
                                    :loading="isSubmitting || goodReceiptStore.isLoading" text="Ubah Penerimaan"
                                    loadingText="Mengubah..." color="orange" icon="save" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
