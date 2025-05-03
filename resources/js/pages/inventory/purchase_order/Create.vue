<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePurchaseOrderStore } from '@/stores/purchaseOrderStore';
import { Supplier } from '@/types/supplier';
import { Product } from '@/types/produk';
import { Unit } from '@/types/unit';
import { UnitConversion } from '@/types/unit_conversion';
import { StatusPoOption } from '@/types/status_po';
import { PurchaseOrder, PurchaseOrderDetail } from '@/types/purchase_order';
import BaseButton from '@/components/BaseButton.vue';
import DateInput from '@/components/DateInput.vue';
import VSelect from '@/components/VSelect.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import TextInput from '@/components/TextInput.vue';
import { User2, Activity, Percent, Calendar } from 'lucide-vue-next';
import { formatCurrency } from '@/helpers/currencyHelper';
import TextNumber from '@/components/TextNumber.vue';

const props = defineProps<{
    title: string;
    desc: string;
    suppliers: Supplier[] | null;
    products: Product[];
    unit_conversions: UnitConversion[];
    units: Unit[] | any;
    status: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
    po_number: string
}>();

const purchaseOrderStore = usePurchaseOrderStore();
const namaPetugas = props.currentUser?.name || 'PETUGAS Laville.id';
const tanggalPO = new Date().toISOString().split('T')[0];

// Form dengan validasi dan submit handling
const form = ref({
    po_number: props.po_number,
    supplier_id: 0,
    user_id: props.currentUser?.id || null,
    user_ack_id: null,
    user_reject_id: null,
    po_date: '',
    expected_date: '',
    ack_date: null,
    reject_date: null,
    subtotal: 0,
    tax: 0,
    discount: 0,
    total_net: 0,
    status: 'PROSESS',
    notes: '',
    is_active: true,
    taxAmount: 0,
    details: [createEmptyDetail()],
});

// Membuat detail kosong untuk pemesanan produk
function createEmptyDetail(): PurchaseOrderDetail {
    return {
        product_id: 0,
        unit_id: 0,
        quantity: 1,
        base_quantity: 0,
        price: 0,
        subtotal: 0,
        received_quantity: 0,
        received_base_quantity: 0
    };
}

// Menambah item baru
function addItem() {
    form.value.details.push(createEmptyDetail());
}

// Menghapus item
function removeItem(index: number) {
    if (form.value.details.length > 1) {
        form.value.details.splice(index, 1);
        calculateTotals();
    }
}

// Cari produk berdasarkan ID
function findProduct(productId: number | string): Product | undefined {
    return props.products.find(product => product.id == productId);
}

// Cari unit berdasarkan ID
function findUnit(unitId: number | string): Unit | undefined {
    return props.units.find((unit: Unit) => unit.id == unitId);
}

// Ketika produk dipilih, set unit default
function handleProductChange(index: number) {
    const detail = form.value.details[index];
    const product = findProduct(detail.product_id);

    if (product && product.defaultUnit) {
        form.value.details[index].unit_id = product.defaultUnit.id;
        form.value.details[index].price = product.purchase_price || 0;
        calculateItemSubtotal(index);
    }
}

// Hitung subtotal untuk item
function calculateItemSubtotal(index: number) {
    const detail = form.value.details[index];
    detail.subtotal = detail.quantity * detail.price;
    calculateTotals();
}

// Hitung base quantity berdasarkan unit conversion
function calculateBaseQuantity(index: number) {
    const detail = form.value.details[index];
    const product = findProduct(detail.product_id);
    const unit = findUnit(detail.unit_id);

    if (product && unit) {
        const conversion = props.unit_conversions.find(
            uc => uc.product_id == product.id && uc.from_unit_id == unit.id
        );

        if (conversion) {
            detail.base_quantity = detail.quantity * conversion.conversion_factor;
            detail.received_base_quantity = detail.quantity * conversion.conversion_factor;
            detail.received_quantity = detail.quantity;
        } else {
            detail.base_quantity = detail.quantity;
            detail.received_base_quantity = detail.quantity;
            detail.received_quantity = detail.quantity;
        }
    }

    calculateItemSubtotal(index);
}

// Hitung total keseluruhan
function calculateTotals() {
    form.value.subtotal = form.value.details.reduce((sum, detail) => sum + detail.subtotal, 0);
    const discountAmount = form.value.discount || 0;
    const subtotalWithDiscount = form.value.subtotal - discountAmount;
    const taxAmount = form.value.tax ? (subtotalWithDiscount * (form.value.tax / 100)) : 0;
    form.value.total_net = subtotalWithDiscount + taxAmount;
    form.value.taxAmount = taxAmount;
}

// Handle submit form
function submitForm() {
    purchaseOrderStore.createPurchaseOrder(form.value);
}

// Watch untuk perubahan tax dan discount
function handleTaxDiscountChange() {
    calculateTotals();
}

// Computed property untuk supplier options
const supplierOptions = computed(() => {
    return props.suppliers?.map(supplier => ({
        id: supplier.id,
        name: supplier.name
    })) || [];
});

// Computed property untuk product options
const productOptions = computed(() => {
    return props.products.map(product => ({
        id: product.id,
        name: `${product.code} - ${product.name}`
    }));
});

// Computed property untuk unit options berdasarkan product
function getUnitOptions(productId: number | string) {
    const product = findProduct(productId);
    if (!product) return [];

    const units: { id: number; name: string }[] = [];

    if (product.defaultUnit) {
        units.push({
            id: product.defaultUnit.id,
            name: product.defaultUnit.name,
        });
    }

    if (product.unitConversions && product.unitConversions.length > 0) {
        product.unitConversions.forEach(uc => {
            const fromUnitExists = units.some(u => u.id === uc.from_unit_id);
            const toUnitExists = units.some(u => u.id === uc.to_unit_id);

            if (!fromUnitExists && uc.fromUnit && uc.from_unit_id != null) {
                units.push({
                    id: Number(uc.from_unit_id),
                    name: uc.fromUnit.name,
                });
            }

            if (!toUnitExists && uc.toUnit && uc.to_unit_id != null) {
                units.push({
                    id: Number(uc.to_unit_id),
                    name: uc.toUnit.name,
                });
            }
        });
    }

    return units;
}


// Auto-calculate on mount
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
                            <!-- Kiri: Title and Description -->
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>

                            <!-- Kanan: Petugas Info -->
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                                    <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sekarang</div>
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ tanggalPO }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <!-- Kolom Kiri -->
                                <div class="space-y-6">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-purple-100 dark:bg-purple-900 p-2 rounded-full">
                                            <User2 class="w-6 h-6 text-purple-600 dark:text-purple-300" />
                                        </div>

                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Pembuat PO</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ namaPetugas }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <div class="bg-orange-100 dark:bg-orange-900 p-2 rounded-full">
                                            <Activity class="w-6 h-6 text-orange-600 dark:text-orange-300" />
                                        </div>

                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ form.status }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Nomor PO -->
                                    <div class="mt-4">
                                        <TextInput id="po_number" v-model="form.po_number" label="No.PO"
                                            placeholder="Masukkan No.PO" required :readonly="true" />
                                    </div>

                                    <!-- Supplier -->
                                    <div>
                                        <VSelect id="supplier_id" v-model="form.supplier_id" :options="supplierOptions"
                                            label="Supplier" placeholder="Pilih Supplier" option-label="name"
                                            option-value="id" required />
                                    </div>



                                    <div class="flex items-center justify-between mb-2">
                                        <!-- Tanggal PO -->
                                        <DateInput id="po_date" v-model="form.po_date" label="Tanggal PO"
                                            placeholder="Masukkan Tanggal PO" required class="w-full sm:w-[48%]" />

                                        <!-- Tanggal Expektasi PO di sebelah kanan -->
                                        <DateInput id="po_date" v-model="form.expected_date"
                                            label="Tanggal Expektasi PO" placeholder="Masukkan Tanggal Expektasi PO"
                                            required class="w-full sm:w-[48%]" />
                                    </div>

                                </div>

                                <!-- Kolom Kanan -->
                                <div class="space-y-6">
                                    <!-- Catatan -->
                                    <div>
                                        <TextAreaInput v-model="form.notes" id="notes" label="Catatan"
                                            placeholder="Masukkan Catatan" :rows="18" :maxlength="900" :required="false" />
                                    </div>
                                </div>
                            </div>

                            <div class="h-auto">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Detail Item
                                    Purchase Order</h3>

                                <div class="my-4">
                                    <button type="button" @click="addItem"
                                        class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Item
                                    </button>
                                </div>

                                <!-- Bagian Tabel Item -->
                                <div class="overflow-x-auto mb-2">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                        <thead class="bg-gray-800 dark:bg-gray-200">
                                            <tr>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Produk
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Unit
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Jumlah
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Harga
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Subtotal
                                                </th>
                                                <th scope="col"
                                                    class="px-4 py-3 text-left text-xs font-medium text-gray-900 dark:text-gray-900 uppercase tracking-wider">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 rounded divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-for="(detail, index) in form.details" :key="index">
                                                <td class="px-4 py-2 w-64">
                                                    <VSelect id="product_id" v-model="form.details[index].product_id"
                                                        :options="productOptions" option-label="name"
                                                        @update:modelValue="handleProductChange(index)"
                                                        option-value="id" required />
                                                </td>

                                                <td class="px-4 py-2 w-64">
                                                    <VSelect v-model="form.details[index].unit_id"
                                                        :options="getUnitOptions(detail.product_id)" class="w-full"
                                                        placeholder="Unit"
                                                        @update:modelValue="calculateBaseQuantity(index)" required />
                                                </td>

                                                <td class="px-4 py-2">
                                                    <TextNumber id="quantity"
                                                        v-model.number="form.details[index].quantity"
                                                        @input="calculateBaseQuantity(index)" />
                                                </td>

                                                <td class="px-4 py-2">
                                                    <TextNumber id="price" v-model="form.details[index].price"
                                                        @input="calculateItemSubtotal(index)" />
                                                </td>

                                                <td class="px-4 py-2">
                                                    <div class="text-gray-900 dark:text-gray-100">
                                                        {{ formatCurrency(detail.subtotal) }}
                                                    </div>
                                                </td>

                                                <td class="px-4 py-2">
                                                    <button type="button" @click="removeItem(index)"
                                                        class="text-red-500 hover:text-red-700"
                                                        :disabled="form.details.length <= 1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Bagian Subtotal dan Total -->
                                <div class="mt-8 flex justify-end">
                                    <div class="w-full md:w-1/3">
                                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow-md">
                                            <!-- Subtotal -->
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-sm text-gray-600 dark:text-gray-300">Subtotal:</span>
                                                <span class="font-medium">{{ formatCurrency(form.subtotal) }}</span>
                                            </div>

                                            <hr class="border-t-4 border-gray-600 my-4">

                                            <!-- Diskon -->
                                            <div class="mb-4">
                                                <div class="flex justify-between items-center mb-1">
                                                    <label for="discount"
                                                        class="text-sm text-gray-600 dark:text-gray-300">
                                                        Diskon:
                                                    </label>
                                                    <div class="w-1/2">
                                                        <TextNumber id="discount" v-model.number="form.discount"
                                                            @input="handleTaxDiscountChange" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pajak (PPN) -->
                                            <div class="mb-2">
                                                <div class="flex justify-between items-center mb-1">
                                                    <label for="tax" class="text-sm text-gray-600 dark:text-gray-300">
                                                        Pajak (PPN):
                                                    </label>
                                                    <div class="flex items-center w-1/2 space-x-2">
                                                        <TextNumber id="tax" v-model.number="form.tax"
                                                            @input="handleTaxDiscountChange" class="w-full" />
                                                        <Percent class="text-red-500 dark:text-red-500" />
                                                    </div>
                                                </div>
                                                <div class="flex justify-between items-center mt-1">
                                                    <span class="text-sm text-gray-600 dark:text-gray-300">Total
                                                        Pajak:</span>
                                                    <span
                                                        class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                        {{ formatCurrency(form.taxAmount) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Total -->
                                            <div
                                                class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                                                <span
                                                    class="font-semibold text-gray-800 dark:text-gray-200">Total:</span>
                                                <span class="font-bold text-lg text-blue-600 dark:text-blue-400">
                                                    {{ formatCurrency(form.total_net) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link :href="route('admin.inventory.purchase_order.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                           bg-white px-4 py-2 text-sm font-medium text-gray-700
                                           shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                           focus:ring-primary-500 focus:ring-offset-2
                                           dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                           dark:hover:bg-gray-600 transition-colors">
                                Batal
                                </Link>
                                <BaseButton :loading="purchaseOrderStore.isLoading" text="Simpan"
                                    loadingText="Menyimpan..." color="blue" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
