<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Product } from '@/types/produk';
import { Unit } from '@/types/unit';
import { UnitConversion } from '@/types/unit_conversion';
import { StatusPoOption } from '@/types/status_po';
import { StockOpnameDetail } from '@/types/stock_opname';
import BaseButton from '@/components/BaseButton.vue';
import DateInput from '@/components/DateInput.vue';
import VSelect from '@/components/VSelect.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import TextInput from '@/components/TextInput.vue';
import TextNumber from '@/components/TextNumber.vue';
import { Calendar, Search, Package, Loader2, Percent, User2, AlertTriangle, Check, X } from 'lucide-vue-next';
import { formatCurrency } from '@/helpers/currencyHelper';
import { useStockOpnameStore } from '@/stores/stockOpnameStore';
import CheckboxInput from '@/components/CheckboxInput.vue';

const props = defineProps<{
    title: string;
    desc: string;
    products: Product[];
    unit_conversions: UnitConversion[];
    units: Unit[] | any;
    status_so_details: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
    so_number: string
}>();

const stockOpnameStore = useStockOpnameStore();
const tanggalStockOpname = new Date().toISOString().split('T')[0];
const isSubmitting = ref(false);
const searchQuery = ref('');
const showDifferencesOnly = ref(false);

const form = ref({
    so_number: props.so_number,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    user_id: props.currentUser?.id || 0,
    so_date: tanggalStockOpname,
    user_validator_id: null,
    validator_date: null,
    user_ack_id: null,
    ack_date: null,
    user_reject_id: null,
    reject_date: null,
    subtotal: 0,
    tax: 0,
    discount: 0,
    total_net: 0,
    status: 'DRAFT',
    notes: '',
    is_locked: false,
    is_active: true,
    details: [] as StockOpnameDetail[]
});

onMounted(() => {
    initializeStockOpnameDetails();
});

const initializeStockOpnameDetails = () => {
    form.value.details = props.products.map(product => {
        const currentStock = product.currentStocks?.[0] || { quantity: 0, base_quantity: 0 };

        return {
            product_id: product.id,
            unit_id: product.default_unit_id || 0,
            system_stock: currentStock.quantity || 0,
            system_stock_base: currentStock.base_quantity || 0,
            physical_stock: currentStock.quantity || 0,
            physical_stock_base: currentStock.base_quantity || 0,
            difference_stock: 0,
            difference_stock_base: 0,
            price: product.purchase_price || 0,
            subtotal: 0,
            status: '',
            notes: '',
            created_at: tanggalStockOpname,
            product: product,
            unit: product.defaultUnit || undefined
        };
    });

    calculateTotals();
};

const filteredStockDetails = computed(() => {
    let filtered = form.value.details;

    // Apply search filter if query exists
    if (searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(detail =>
            detail.product?.name.toLowerCase().includes(query) ||
            detail.product?.code.toLowerCase().includes(query)
        );
    }

    // Filter to show only items with differences if option is selected
    if (showDifferencesOnly.value) {
        filtered = filtered.filter(detail => detail.difference_stock !== 0);
    }

    return filtered;
});

const statusStockOpnameDetailOptions = computed(() => {
    return props.status_so_details?.map(status_so_detail => ({
        id: status_so_detail.label,
        name: status_so_detail.name
    })) || [];
});

const getUnitOptionsForProduct = (productId: number) => {
    const unitConversions = props.unit_conversions.filter(uc => uc.product_id === productId);
    const units = unitConversions.map(uc => uc.fromUnit || uc.toUnit).filter(Boolean);

    // Add default unit if not already in the list
    const product = props.products.find(p => p.id === productId);
    if (product?.defaultUnit && !units.some(u => u?.id === product.defaultUnit?.id)) {
        units.push(product.defaultUnit);
    }

    return units.map(unit => ({
        id: unit?.id ?? 0,
        name: unit?.name ?? ''
    }));
};

const updatePhysicalStock = (index: number, value: number) => {
    const detail = form.value.details[index];
    detail.physical_stock = value;

    const product = props.products.find(p => p.id === detail.product_id);

    const unitConversion = props.unit_conversions.find(uc =>
        uc.product_id === detail.product_id &&
        uc.from_unit_id === detail.unit_id
    );

    if (unitConversion) {
        detail.physical_stock_base = detail.physical_stock * unitConversion.conversion_factor;
    } else {
        detail.physical_stock_base = detail.physical_stock;
    }

    detail.difference_stock = detail.physical_stock - detail.system_stock;
    detail.difference_stock_base = detail.physical_stock_base - detail.system_stock_base;
    detail.subtotal = detail.difference_stock * detail.price;

    if (detail.difference_stock === 0) {
        detail.status = 'MATCH';
    } else if (detail.difference_stock > 0) {
        detail.status = 'OVERSTOCK';
    } else if (detail.difference_stock < 0) {
        detail.status = 'SHORTAGE';
    }

    calculateTotals();
};


const changeUnit = (index: number, unitId: number) => {
    const detail = form.value.details[index];
    detail.unit_id = unitId;
    detail.unit = props.units.find((u: Unit) => u.id === unitId);

    const product = props.products.find(p => p.id === detail.product_id);

    if (product) {
        const unitConversion = props.unit_conversions.find(uc =>
            uc.product_id === product.id &&
            uc.from_unit_id === unitId
        );

        const currentStock = product.currentStocks?.[0] || { quantity: 0, base_quantity: 0 };

        if (unitConversion) {
            detail.system_stock = currentStock.base_quantity / unitConversion.conversion_factor;
        } else if (unitId === product.default_unit_id) {
            detail.system_stock = currentStock.quantity;
        } else {
            detail.system_stock = currentStock.base_quantity;
        }

        detail.physical_stock = detail.system_stock;

        updatePhysicalStock(index, detail.physical_stock);
    }
};


const calculateTotals = () => {
    form.value.subtotal = form.value.details.reduce((sum, detail) => sum + detail.subtotal, 0);

    const absSubtotal = Math.max(0, form.value.subtotal);
    const discountAmount = (absSubtotal * form.value.discount) / 100;
    const afterDiscount = absSubtotal - discountAmount;
    const taxAmount = (afterDiscount * form.value.tax) / 100;
    form.value.total_net = form.value.subtotal - (form.value.subtotal < 0 ? 0 : discountAmount + taxAmount);
};

watch(() => form.value.tax, calculateTotals);
watch(() => form.value.discount, calculateTotals);

const isFormValid = computed(() => {
    return form.value.notes !== '' &&
        form.value.details &&
        form.value.details.length > 0 &&
        form.value.details.every(detail => detail.unit_id !== 0) &&
        form.value.details.every(detail => detail.product_id !== 0) &&
        form.value.details.every(detail => detail.notes !== '')
});

const submitForm = () => {
    if (!isFormValid.value) {
        return;
    }

    isSubmitting.value = true;
    stockOpnameStore.createStockOpname(form.value);
};
</script>
<template>
    <AppLayout>

        <Head :title="title" />
        <div class="py-8">
            <form @submit.prevent="submitForm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Header Card with improved spacing and visual hierarchy -->

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-xl mb-8">
                        <div class="p-8">
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
                                            {{ tanggalStockOpname }}
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Stock Opname Info with better spacing -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
                                <div>
                                    <div class="mb-6">
                                        <TextInput id="so_number" v-model="form.so_number" label="Nomor Stock Opname"
                                            placeholder="Nomor SO" readonly />
                                    </div>
                                    <div class="mb-6">
                                        <DateInput id="so_date" v-model="form.so_date" label="Tanggal Stock Opname"
                                            placeholder="Pilih Tanggal" />
                                    </div>
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                                            <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sekarang</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ tanggalStockOpname }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="bg-purple-100 dark:bg-purple-900 rounded-lg p-2">
                                            <User2 class="w-5 h-5 text-purple-600 dark:text-purple-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sekarang</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                {{ currentUser?.name }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 mb-6">
                                        <div class="bg-orange-100 dark:bg-orange-900 rounded-lg p-2">
                                            <Loader2 class="w-5 h-5 text-orange-600 dark:text-orange-300" />
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                                            <div class="font-medium text-gray-800 dark:text-white">
                                                PROSESS
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <TextAreaInput id="notes" v-model="form.notes" label="Catatan"
                                            placeholder="Masukan catatan (jika ada)" :rows="6" />
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:bg-gray-800 p-6 rounded-lg shadow-sm space-y-4">
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Detail Stock Opname
                                    </h2>

                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        <strong>Stock Opname</strong> adalah proses pemeriksaan dan pencatatan ulang
                                        terhadap jumlah fisik barang yang tersedia di gudang, untuk memastikan
                                        kesesuaian
                                        antara data sistem dan realita di lapangan.
                                    </p>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Status
                                            Stock
                                            Opname:</h3>
                                        <ul
                                            class="list-disc list-inside text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-full">DRAFT</span>
                                                – Data stock opname masih dalam tahap penyusunan dan belum final.
                                            </li>
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-orange-200 dark:bg-orange-700 text-orange-800 dark:text-white rounded-full">VALIDATED</span>
                                                – Data stock opname di kunci/locked serasa sudah sesuai dengan barang fisik.
                                            </li>
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-green-200 dark:bg-green-700 text-green-800 dark:text-white rounded-full">COMPLETED</span>
                                                – Proses stock opname telah selesai dan disimpan secara permanen.
                                            </li>
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-red-200 dark:bg-red-700 text-red-800 dark:text-white rounded-full">CANCELED</span>
                                                – Proses stock opname dibatalkan dan tidak digunakan.
                                            </li>
                                        </ul>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Kondisi
                                            Selisih Stok:</h3>
                                        <ul
                                            class="list-disc list-inside text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-200 dark:bg-blue-700 text-blue-800 dark:text-white rounded-full">MATCH</span>
                                                – Jumlah fisik sesuai dengan jumlah sistem →
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-green-200 dark:bg-green-700 text-green-800 dark:text-white rounded-full">IN</span>
                                            </li>
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-yellow-200 dark:bg-yellow-700 text-yellow-800 dark:text-white rounded-full">SHORTAGE</span>
                                                – Jumlah fisik lebih sedikit dari sistem →
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-red-200 dark:bg-red-700 text-red-800 dark:text-white rounded-full">OUT</span>
                                            </li>
                                            <li>
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-indigo-200 dark:bg-indigo-700 text-indigo-800 dark:text-white rounded-full">OVERSTOCK</span>
                                                – Jumlah fisik lebih banyak dari sistem →
                                                <span
                                                    class="inline-block px-2 py-0.5 text-xs font-medium bg-green-200 dark:bg-green-700 text-green-800 dark:text-white rounded-full">IN</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        Data selisih ini akan digunakan untuk melakukan penyesuaian pada stok sistem,
                                        baik
                                        penambahan maupun pengurangan, sesuai kondisi riil di lapangan.
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Product Listing Card with improved table design -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-xl mb-8">
                        <div class="p-8">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Daftar Produk</h2>

                            <!-- Search and Filters with improved layout -->
                            <div class="flex flex-col md:flex-row gap-6 mb-8">
                                <div class="flex-grow relative">
                                    <TextInput id="search_query" v-model="searchQuery" label="Cari Produk"
                                        placeholder="Masukkan nama atau kode produk" />
                                </div>
                                <div class="flex items-end pb-1">
                                    <CheckboxInput id="showDifferencesOnly" v-model="showDifferencesOnly"
                                        label="Tampilkan hanya yang berbeda" />
                                </div>
                            </div>

                            <!-- Enhanced Products Table -->
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Nama Produk
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Unit <span class="text-red-500">*</span>
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Stok Sistem
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Stok Fisik <span class="text-red-500">*</span>
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Selisih
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Catatan <span class="text-red-500">*</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="filteredStockDetails.length > 0"
                                        class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="(detail, index) in filteredStockDetails" :key="detail.product_id"
                                            :class="{
                                                'bg-blue-50 dark:bg-blue-900/20': detail.difference_stock > 0,
                                                'bg-red-50 dark:bg-red-900/20': detail.difference_stock < 0,
                                                'bg-green-50 dark:bg-green-900/20': detail.difference_stock === 0
                                            }">
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                                    detail.product?.name }}</div>
                                                <div v-if="detail.product?.code"
                                                    class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ detail.product.code }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <VSelect :id="`unit_${detail.product_id}`" v-model="detail.unit_id"
                                                    :options="getUnitOptionsForProduct(detail.product_id)"
                                                    option-label="name" option-value="id" placeholder="Pilih Unit"
                                                    class="w-36" @update:modelValue="changeUnit(index, $event)" />
                                            </td>
                                            <td
                                                class="px-6 py-5 whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ detail.system_stock.toFixed(2) }}
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <TextNumber :id="`physical_stock_${detail.product_id}`"
                                                    v-model="detail.physical_stock" placeholder="0" class="w-28"
                                                    @update:modelValue="updatePhysicalStock(index, parseFloat($event))" />
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap" :class="{
                                                'text-green-600 dark:text-green-400': detail.difference_stock > 0,
                                                'text-red-600 dark:text-red-400': detail.difference_stock < 0,
                                                'text-gray-500 dark:text-gray-300': detail.difference_stock === 0
                                            }">
                                                <div class="flex items-center text-sm font-medium">
                                                    <span v-if="detail.difference_stock > 0">+</span>
                                                    {{ detail.difference_stock.toFixed(2) }}
                                                    <Check v-if="detail.difference_stock === 0"
                                                        class="ml-2 w-5 h-5 text-green-500" />
                                                    <AlertTriangle v-else class="ml-2 w-5 h-5" :class="{
                                                        'text-green-500': detail.difference_stock > 0,
                                                        'text-red-500': detail.difference_stock < 0
                                                    }" />
                                                </div>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <span
                                                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': detail.status === 'MATCH',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': detail.status === 'SHORTAGE',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': detail.status === 'OVERSTOCK'
                                                    }">
                                                    {{ detail.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5 whitespace-nowrap">
                                                <TextInput id="notes" v-model="detail.notes" label=""
                                                    placeholder="Catatan" class="w-full" />
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="8"
                                                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                                <div class="flex flex-col items-center justify-center">
                                                    <Package class="w-16 h-16 text-gray-400 mb-3" />
                                                    <p class="text-lg">Tidak ada produk yang ditemukan</p>
                                                    <p class="text-sm mt-2 text-gray-400">Coba ubah filter pencarian</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card with improved layout -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-md rounded-xl mb-6">
                        <div class="p-8">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Ringkasan</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Left: Summary Info with better visual design -->
                                <div>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-xl shadow-sm">
                                        <div
                                            class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium">Total
                                                Produk:</span>
                                            <span class="font-semibold text-lg text-gray-900 dark:text-white">{{
                                                form.details.length }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium">Produk dengan
                                                Selisih:</span>
                                            <span class="font-semibold text-lg text-gray-900 dark:text-white">{{
                                                form.details.filter(d => d.difference_stock !== 0).length}}</span>
                                        </div>
                                        <div class="flex justify-between py-3">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium">Status:</span>
                                            <span
                                                class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                {{ form.status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Financial Summary with improved layout -->
                                <div>
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-xl shadow-sm">
                                        <div
                                            class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-600">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium">Subtotal:</span>
                                            <span class="font-semibold text-lg text-gray-900 dark:text-white">{{
                                                formatCurrency(form.subtotal) }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center">
                                                <span class="text-gray-600 dark:text-gray-300 font-medium">Diskon
                                                    (%):</span>
                                                <TextNumber id="discount" v-model="form.discount"
                                                    class="ml-3 w-20 text-sm" placeholder="0" />
                                            </div>
                                            <span class="font-semibold text-lg text-gray-900 dark:text-white">{{
                                                formatCurrency(form.subtotal * form.discount / 100) }}</span>
                                        </div>
                                        <div
                                            class="flex justify-between py-3 border-b border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center">
                                                <span class="text-gray-600 dark:text-gray-300 font-medium">Pajak
                                                    (%):</span>
                                                <TextNumber id="tax" v-model="form.tax" class="ml-3 w-20 text-sm"
                                                    placeholder="0" />
                                            </div>
                                            <span class="font-semibold text-lg text-gray-900 dark:text-white">{{
                                                formatCurrency(form.subtotal * form.tax / 100) }}</span>
                                        </div>
                                        <div class="flex justify-between py-3 mt-2">
                                            <span
                                                class="text-gray-800 dark:text-gray-200 font-bold text-lg">Total:</span>
                                            <span class="text-gray-900 dark:text-white font-bold text-xl">{{
                                                formatCurrency(form.total_net) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Button section with improved styling -->
                        <div
                            class="flex items-center justify-end space-x-4 px-8 py-6 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600">
                            <Link :href="route('admin.inventory.stock_opname.index')" class="inline-flex justify-center rounded-lg border border-gray-300
                                    bg-white px-5 py-2.5 text-sm font-medium text-gray-700
                                    shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                    focus:ring-primary-500 focus:ring-offset-2
                                    dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                    dark:hover:bg-gray-600 transition-colors">
                            Batal
                            </Link>
                            <BaseButton :loading="stockOpnameStore.isLoading" text="Simpan" loadingText="Menyimpan..."
                                color="blue" class="px-6 py-2.5 rounded-lg font-medium" :disabled="!isFormValid" />

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
