<script setup lang="ts">
import { ref, computed, reactive, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import { Product } from '@/types/produk';
import { StatusPoOption } from '@/types/status_po';
import BaseButton from '@/components/BaseButton.vue';
import { Calendar, Plus, Trash2, ShoppingCart, CreditCard, Receipt, User, FileText, CheckCircle, AlertCircle, XCircle } from 'lucide-vue-next';
import { Customer } from '@/types/customer';
import { useTransactionStore } from '@/stores/transactionStore';
import { Transaction, TransactionDetail, TransactionPayment } from '@/types/transaction';
import VSelect from '@/components/VSelect.vue';
import TextInput from '@/components/TextInput.vue';
import DateInput from '@/components/DateInput.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import TextNumber from '@/components/TextNumber.vue';
import { formatNumber } from '@/helpers/currencyHelper';
import { useToast } from 'vue-toastification';

const props = defineProps<{
    title: string;
    desc: string;
    products: Product[];
    customers: Customer[];
    sources: StatusPoOption[];
    type_payments: StatusPoOption[];
    invoice_number: string;
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const toast = useToast();
const transactionStore = useTransactionStore();
const isSubmitting = ref(false);
const tanggalSekarang = new Date().toISOString().split('T')[0];

type TransactionFormData = Transaction & {
    details: TransactionDetail[];
    payments: TransactionPayment[];
    [key: string]: any;
};

const form = useForm<TransactionFormData>({
    invoice_number: props.invoice_number,
    slug: '',
    customer_id: null,
    transaction_date: tanggalSekarang,
    user_id: props.currentUser?.id || null,
    total: 0,
    discount: 0,
    subtotal: 0,
    tax: 0,
    tax_amount: 0,
    total_amount: 0,
    paid_amount: 0,
    change_amount: 0,
    status: '',
    source_transaction: '',
    notes: '',
    is_active: true,
    created_at: tanggalSekarang,
    details: [createEmptyDetail()],
    payments: [createEmptyPayment()]
});


// Keep track of UI state
const activeTab = ref<'products' | 'payment'>('products');

// Mapping data customer, product and source transaction
const customerOptions = computed(() => {
    if (!Array.isArray(props.customers)) return [];

    return props.customers.map(customer => ({
        id: customer.id,
        name: customer.name
    }));
});

const sourceTransactions = computed(() => {
    if (!Array.isArray(props.sources)) return [];

    return props.sources.map(source => ({
        id: source.label,
        name: source.name
    }));
});

const productOptions = computed(() => {
    if (!Array.isArray(props.products)) return [];

    return props.products.map(product => ({
        id: product.id,
        name: product.name
    }));
});

const paymentMethods = computed(() => {
    if (!Array.isArray(props.type_payments)) return [];

    return props.type_payments.map(type_payment => ({
        id: type_payment.label,
        name: type_payment.name
    }));
});



// Create empty detail record
function createEmptyDetail(): TransactionDetail {
    return {
        product_id: 0,
        unit_id: 0,
        quantity: 0,
        base_quantity: 0,
        price: 0,
        discount: 0,
        subtotal: 0,
        product: undefined,
        unit: undefined
    };
}

// Create empty payment record
function createEmptyPayment(): TransactionPayment {
    return {
        payment_date: tanggalSekarang,
        payment_method: '',
        payment_reference: '',
        amount: 0,
        status: 'PAID'
    };
}

// Add product to transaction
function addProduct() {
    form.details.push(createEmptyDetail());
}

// Add payment method
function addPayment() {
    form.payments.push(createEmptyPayment());
}

// Remove product from transaction
function removeProduct(index: number) {
    if (form.details.length > 1) {
        form.details.splice(index, 1);
        calculateTotals();
    }
}

// Remove payment method
function removePayment(index: number) {
    if (form.payments.length > 1) {
        form.payments.splice(index, 1);
        calculateTotals();
    }
}

// Handle product selection
function onProductChange(index: number, productId: number) {
    const product = props.products.find(p => p.id === parseInt(productId.toString()));

    if (product) {
        form.details[index].product_id = product.id;
        form.details[index].product = product;
        form.details[index].price = product.selling_price || 0;

        form.details[index].unit_id = product.unitConversions?.[0]?.from_unit_id ?? 0;

        // Reset quantity to 0 when changing product
        form.details[index].quantity = 0;

        // Reset base_quantity to 0 as well
        form.details[index].base_quantity = 0;

        // Get conversion factor from unitConversions if available
        const conversionFactor = product.unitConversions?.[0]?.conversion_factor || 1;
        form.details[index].conversion_factor = conversionFactor;

        calculateItemSubtotal(index);
    }
}

// Calculate subtotal for a line item
function calculateItemSubtotal(index: number) {
    const item = form.details[index];
    const product = item.product;
    const conversionFactor = item.conversion_factor || 1;

    // Get current stock from product's currentStocks
    const currentStock = product?.currentStocks?.[0]?.quantity || 0;

    // Parse values
    let quantity = parseFloat(item.quantity.toString()) || 0;
    const price = parseFloat(item.price.toString()) || 0;
    const discount = parseFloat(item.discount.toString()) || 0;

    // Validate quantity doesn't exceed available stock
    if (quantity > currentStock) {
        quantity = currentStock;
        item.quantity = currentStock;
        toast.info(`Kuantitas produk ${product?.name} melebihi stok tersedia (${currentStock})`);
    }

    // Calculate base quantity using conversion factor
    item.base_quantity = quantity * conversionFactor;

    item.subtotal = quantity * price - discount;
    calculateTotals();
}

// Calculate all transaction totals
function calculateTotals() {
    form.total = form.details.reduce((sum, item) => {
        return sum + item.subtotal;
    }, 0);

    form.subtotal = form.total - form.discount;
    form.tax_amount = form.tax ? (form.subtotal * (form.tax / 100)) : 0;
    form.total_amount = form.subtotal + form.tax_amount;

    form.paid_amount = form.payments.reduce((sum, payment) => {
        return sum + parseFloat(payment.amount.toString() || '0');
    }, 0);

    form.change_amount = form.paid_amount - form.total_amount;

    if (form.paid_amount >= form.total_amount) {
        form.status = 'PAID';
    } else if (form.paid_amount > 0) {
        form.status = 'PARTIAL';
    } else {
        form.status = 'PENDING';
    }
}

// Handle form submission
function submitForm() {
    isSubmitting.value = true;
    transactionStore.createTransaction(form);
}

// Watch for changes and recalculate
onMounted(() => {
    calculateTotals();
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
                        <div class="flex justify-between items-center mb-6">
                            <!-- Left: Title and Description -->
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>

                            <!-- Right: Date Info -->
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                                    <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sekarang</div>
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ tanggalSekarang }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitForm">

                            <!-- Transaction Header Info -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Invoice Number -->
                                    <div>
                                        <TextInput id="invoice_number" v-model="form.invoice_number" label="No.Invoice"
                                            placeholder="Generate auto No.Invoice" required :readonly="true" />
                                    </div>

                                    <!-- Customer -->
                                    <div>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <User class="h-4 w-4 text-gray-400" />
                                            </div>
                                            <VSelect id="customer_id" v-model="form.customer_id"
                                                :options="customerOptions" label="Pelanggan (Optional)"
                                                placeholder="Pilih Pelanggan" option-label="name" option-value="id" />
                                        </div>
                                    </div>

                                    <!-- Transaction Date -->
                                    <div>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Calendar class="h-4 w-4 text-gray-400" />
                                            </div>
                                            <DateInput id="transaction_date" v-model="form.transaction_date"
                                                label="Tanggal Transaksi" placeholder="Tanggal Transaksi" required
                                                class="w-full" />
                                        </div>
                                    </div>

                                    <!-- Source Transaction -->
                                    <div>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <ShoppingCart class="h-4 w-4 text-gray-400" />
                                            </div>
                                            <VSelect id="source_transaction" v-model="form.source_transaction"
                                                :options="sourceTransactions" label="Source Transaksi"
                                                placeholder="Pilih Source Transaksi" option-label="name"
                                                option-value="id" required />
                                        </div>
                                    </div>

                                    <!-- Notes -->
                                    <div class="md:col-span-2">
                                        <div class="relative">
                                            <TextAreaInput v-model="form.notes" id="notes" label="Catatan"
                                                placeholder="Masukkan Catatan" :rows="2" :maxlength="900"
                                                :required="false" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Navigation -->
                            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                                <nav class="-mb-px flex">
                                    <button type="button" @click="activeTab = 'products'"
                                        class="py-3 px-4 flex items-center space-x-2 font-medium text-sm border-b-2"
                                        :class="activeTab === 'products'
                                            ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                                        <ShoppingCart class="w-4 h-4" />
                                        <span>Produk</span>
                                    </button>
                                    <button type="button" @click="activeTab = 'payment'"
                                        class="ml-8 py-3 px-4 flex items-center space-x-2 font-medium text-sm border-b-2"
                                        :class="activeTab === 'payment'
                                            ? 'border-green-500 text-green-600 dark:text-green-400'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'">
                                        <CreditCard class="w-4 h-4" />
                                        <span>Pembayaran</span>
                                    </button>
                                </nav>
                            </div>

                            <!-- Products Tab -->
                            <div v-show="activeTab === 'products'" class="mb-6">
                                <!-- Products Table -->
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Produk
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Harga
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Stok
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Qty
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Diskon
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Subtotal
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(detail, index) in form.details" :key="index">
                                                <td class="px-4 py-2 w-80">
                                                    <VSelect id="product_id" v-model="detail.product_id"
                                                        :options="productOptions"
                                                        @change="onProductChange(index, detail.product_id)"
                                                        placeholder="Pilih Produk" option-label="name" option-value="id"
                                                        required />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextNumber id="price" v-model.number="detail.price"
                                                        @input="calculateItemSubtotal(index)" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="text-gray-900 dark:text-white font-medium">
                                                        {{ formatNumber(detail.product?.currentStocks?.[0]?.quantity || 0) }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextNumber id="quantity" v-model.number="detail.quantity"
                                                        @input="calculateItemSubtotal(index)" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextNumber id="discount" v-model.number="detail.discount"
                                                        @input="calculateItemSubtotal(index)" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="text-gray-900 dark:text-white font-medium">
                                                        {{ detail.subtotal.toLocaleString('id-ID') }}
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <button type="button" @click="removeProduct(index)"
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                        <Trash2 class="w-5 h-5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Add Product Button -->
                                <div class="mt-3">
                                    <button type="button" @click="addProduct"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Tambah Produk
                                    </button>
                                </div>

                                <!-- Transaction Summary -->
                                <div class="mt-6 w-full flex justify-end">
                                    <div
                                        class="w-full md:w-96 p-3 transition duration-300 ease-in-out transform hover:shadow-lg hover:-translate-y-1 hover:scale-[1.01] bg-gray-50 dark:bg-gray-900 rounded-xl">
                                        <div class="space-y-3 text-sm">
                                            <div class="flex justify-between items-center">
                                                <dt class="text-gray-600 dark:text-gray-300">Total</dt>
                                                <dd class="font-semibold text-gray-900 dark:text-white">
                                                    {{ form.subtotal.toLocaleString('id-ID') }}
                                                </dd>
                                            </div>

                                            <div class="flex flex-col space-y-2">
                                                <label for="discount"
                                                    class="text-gray-600 dark:text-gray-300">Diskon</label>
                                                <TextNumber id="discount" v-model.number="form.discount"
                                                    @input="calculateTotals" />
                                            </div>

                                            <div class="flex justify-between items-center">
                                                <dt class="text-gray-600 dark:text-gray-300">Subtotal</dt>
                                                <dd class="font-semibold text-gray-900 dark:text-white">
                                                    {{ form.subtotal.toLocaleString('id-ID') }}
                                                </dd>
                                            </div>

                                            <div class="flex flex-col space-y-2">
                                                <label for="tax" class="text-gray-600 dark:text-gray-300">Pajak</label>
                                                <div class="flex items-center justify-between gap-4">
                                                    <TextNumber id="tax" v-model.number="form.tax"
                                                        @input="calculateTotals" class="flex-1" />
                                                    <span class="text-gray-900 dark:text-white whitespace-nowrap">
                                                        {{ formatNumber(form.tax_amount) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div
                                                class="border-t border-gray-200 dark:border-gray-600 pt-4 flex justify-between items-center">
                                                <dt class="font-bold text-gray-900 dark:text-white">Total Net</dt>
                                                <dd class="text-xl font-bold text-gray-900 dark:text-white">
                                                    {{ form.total_amount.toLocaleString('id-ID') }}
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Tab -->
                            <div v-show="activeTab === 'payment'" class="mb-6">
                                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Tanggal
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Metode Pembayaran
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Referensi
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Jumlah
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(payment, index) in form.payments" :key="index">
                                                <td class="px-4 py-2">
                                                    <DateInput id="payment_date" v-model="payment.payment_date"
                                                        placeholder="Tanggal Bayar" class="w-full" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <VSelect id="payment_method" v-model="payment.payment_method"
                                                        :options="paymentMethods" placeholder="Pilih Payment Method"
                                                        option-label="name" option-value="id" required />

                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextInput id="payment_reference"
                                                        v-model="payment.payment_reference"
                                                        placeholder="Masukkan Ref.Card" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <TextNumber id="amount" v-model.number="payment.amount"
                                                        @input="calculateTotals" />
                                                </td>
                                                <td class="px-4 py-2">
                                                    <button type="button" @click="removePayment(index)"
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                        <Trash2 class="w-5 h-5" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <!-- Add Payment Button -->
                                <div class="mt-3">
                                    <button type="button" @click="addPayment"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                                        <Plus class="w-4 h-4 mr-2" />
                                        Tambah Pembayaran
                                    </button>
                                </div>

                                <!-- Payment Summary -->
                                <div class="mt-6 w-full flex justify-end">
                                    <div
                                        class="w-full md:w-96 p-4 transition duration-300 ease-in-out transform hover:shadow-lg hover:-translate-y-1 hover:scale-[1.01] bg-gray-50 dark:bg-gray-900 rounded-xl shadow-sm">
                                        <dl class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <dt class="font-normal text-gray-600 dark:text-gray-300">Total Tagihan
                                                </dt>
                                                <dd class="font-medium text-gray-900 dark:text-white">
                                                    {{ form.total_amount.toLocaleString('id-ID') }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between">
                                                <dt class="font-normal text-gray-600 dark:text-gray-300">Total Dibayar
                                                </dt>
                                                <dd class="font-medium text-gray-900 dark:text-white">
                                                    {{ form.paid_amount.toLocaleString('id-ID') }}
                                                </dd>
                                            </div>

                                            <div
                                                class="flex justify-between border-t border-gray-200 dark:border-gray-600 pt-2">
                                                <dt class="font-medium text-gray-900 dark:text-white">
                                                    {{ form.paid_amount >= form.total_amount ? 'Kembalian' : 'Sisa' }}
                                                </dt>
                                                <dd :class="{
                                                    'font-semibold text-lg': true,
                                                    'text-green-600 dark:text-green-400': form.paid_amount >= form.total_amount,
                                                    'text-red-600 dark:text-red-400': form.paid_amount < form.total_amount
                                                }">
                                                    {{ Math.abs(form.change_amount).toLocaleString('id-ID') }}
                                                </dd>
                                            </div>

                                            <div class="flex justify-between pt-2">
                                                <dt class="font-medium text-gray-900 dark:text-white">Status</dt>
                                                <dd :class="{
                                                    'px-2 py-1 text-xs font-medium rounded transition duration-300 ease-in-out': true,
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': form.status === 'PAID',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': form.status === 'PARTIAL',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300': form.status === 'PENDING'
                                                }">
                                                    {{ form.status === 'PAID' ? 'Lunas' : form.status === 'PARTIAL' ?
                                                        'Sebagian' : 'Belum Dibayar' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>


                                <div class="mt-6 flex justify-end space-x-3">
                                    <Link :href="route('admin.transaksi.transaction.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors">
                                    Batal
                                    </Link>
                                    <BaseButton :loading="transactionStore.isLoading" text="Simpan"
                                        loadingText="Menyimpan..." color="blue" />
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
