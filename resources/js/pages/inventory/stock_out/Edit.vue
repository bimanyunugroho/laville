<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Supplier } from '@/types/supplier';
import { Product } from '@/types/produk';
import { Unit } from '@/types/unit';
import { UnitConversion } from '@/types/unit_conversion';
import { StatusPoOption } from '@/types/status_po';
import { StockOut, StockOutDetail } from '@/types/stock_out';
import { useStockOutStore } from '@/stores/stockOutStore';
import { formatCurrency } from '@/helpers/currencyHelper';

// Components
import BaseButton from '@/components/BaseButton.vue';
import DateInput from '@/components/DateInput.vue';
import VSelect from '@/components/VSelect.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import TextInput from '@/components/TextInput.vue';
import TextNumber from '@/components/TextNumber.vue';
import { User2, Activity, Percent, Calendar, Trash2, Plus, X, AlertTriangle } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    desc: string;
    stock_out: StockOut;
    suppliers: Supplier[] | null;
    products: Product[];
    unit_conversions: UnitConversion[];
    units: Unit[] | any;
    status_notes: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const stockOutStore = useStockOutStore();
const tanggalPengeluaranBarang = new Date().toISOString().split('T')[0];

// Form with ref instead of useForm
const form = ref<StockOut>({
    stock_out_number: props.stock_out.stock_out_number,
    supplier_id: props.stock_out.supplier_id || null,
    user_id: props.stock_out.user_id || null,
    out_date: props.stock_out.out_date,
    user_ack_id: null,
    ack_date: null,
    user_reject_id: null,
    reject_date: null,
    subtotal: props.stock_out.subtotal,
    tax: props.stock_out.tax,
    discount: props.stock_out.discount,
    total_net: props.stock_out.total_net,
    status_notes: props.stock_out.status_notes,
    status: props.stock_out.status,
    notes: props.stock_out.notes,
    is_active: props.stock_out.is_active,
    created_at: new Date().toISOString(),
    details: props.stock_out.details ?? []
});

// Form handling
const processing = ref(false);
const showModal = ref(false);
const isSubmitting = ref(false);
const selectedProduct = ref<Product | null>(null);
const selectedUnit = ref<Unit | null>(null);
const tempDetail = ref<StockOutDetail>({
    product_id: 0,
    unit_id: 0,
    quantity: 1,
    base_quantity: 0,
    price: 0,
    subtotal: 0,
    received_quantity: 0,
    received_base_quantity: 0,
    notes_detail: ''
});

// Units available for selected product
const availableUnits = ref<Unit[]>([]);

// Model values for VSelect components
const selectedProductId = ref<number | null>(null);
const selectedUnitId = ref<number | null>(null);

// Computed properties for calculations
const taxAmount = computed(() => {
    return (form.value.subtotal * form.value.tax) / 100;
});

const discountAmount = computed(() => {
    return (form.value.subtotal * form.value.discount) / 100;
});

const totalNet = computed(() => {
    return form.value.subtotal + taxAmount.value - discountAmount.value;
});

// Check if form is valid
const isFormValid = computed(() => {
    return form.value.out_date !== '' &&
        form.value.details &&
        form.value.details.length > 0;
});

// Watch for changes in details to update total
watch(() => form.value.details, () => {
    calculateSubtotal();
}, { deep: true });

// Watch for tax and discount changes
watch([() => form.value.tax, () => form.value.discount], () => {
    updateTotalNet();
});

// Functions
const calculateSubtotal = () => {
    if (!form.value.details) return;

    form.value.subtotal = form.value.details.reduce((sum, detail) => sum + detail.subtotal, 0);
    updateTotalNet();
};

const updateTotalNet = () => {
    form.value.total_net = form.value.subtotal + taxAmount.value - discountAmount.value;
};

const openAddProductModal = () => {
    tempDetail.value = {
        product_id: 0,
        unit_id: 0,
        quantity: 1,
        base_quantity: 0,
        price: 0,
        subtotal: 0,
        received_quantity: 0,
        received_base_quantity: 0,
        notes_detail: ''
    };
    selectedProduct.value = null;
    selectedUnit.value = null;
    selectedProductId.value = null;
    selectedUnitId.value = null;
    availableUnits.value = [];
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleProductSelect = (productId: number) => {
    selectedProductId.value = productId;

    const product = props.products.find(p => p.id === productId);

    if (!product) {
        return;
    }

    selectedProduct.value = product;
    tempDetail.value.product_id = product.id;
    tempDetail.value.price = product.purchase_price;

    selectedUnit.value = null;
    selectedUnitId.value = null;
    tempDetail.value.unit_id = 0;

    availableUnits.value = getUnitOptions(product);

    if (product.defaultUnit) {
        nextTick(() => {
            selectedUnit.value = product.defaultUnit;
            selectedUnitId.value = product.defaultUnit ? product.defaultUnit.id : null;
            tempDetail.value.unit_id = product.defaultUnit ? product.defaultUnit.id : 0;
            calculateBaseQuantity();
        });
    } else {
        if (availableUnits.value.length > 0) {
            nextTick(() => {
                selectedUnit.value = availableUnits.value[0];
                selectedUnitId.value = availableUnits.value[0].id;
                tempDetail.value.unit_id = availableUnits.value[0].id;
                calculateBaseQuantity();
            });
        } else {
            findUnitsFromConversions(product);
        }
    }

    calculateDetailSubtotal();
};

const findUnitsFromConversions = (product: Product) => {
    const relevantConversions = props.unit_conversions.filter(uc =>
        uc.product_id === product.id
    );

    if (relevantConversions.length > 0) {
        const unitIds = new Set<number>();
        relevantConversions.forEach(uc => {
            if (uc.from_unit_id) unitIds.add(uc.from_unit_id);
            if (uc.to_unit_id) unitIds.add(uc.to_unit_id);
        });

        // Dapatkan unit objects
        const units = Array.from(unitIds)
            .map(id => props.units.find((u: Unit) => u.id === id))
            .filter(u => u !== undefined);

        if (units.length > 0) {
            availableUnits.value = units;
            nextTick(() => {
                selectedUnit.value = units[0];
                selectedUnitId.value = units[0].id;
                tempDetail.value.unit_id = units[0].id;
                calculateBaseQuantity();
            });
        }
    } else {
        if (props.units && props.units.length > 0) {
            availableUnits.value = props.units;
            nextTick(() => {
                selectedUnit.value = props.units[0];
                selectedUnitId.value = props.units[0].id;
                tempDetail.value.unit_id = props.units[0].id;
                calculateBaseQuantity();
            });
        }
    }
};

const handleUnitSelect = (unitId: number) => {
    selectedUnitId.value = unitId;

    const unit = props.units.find((u: Unit) => u.id === unitId);

    if (!unit) {
        return;
    }

    selectedUnit.value = unit;
    tempDetail.value.unit_id = unit.id;
    calculateBaseQuantity();
    calculateDetailSubtotal();
};

const calculateBaseQuantity = () => {
    if (!selectedProduct.value || !selectedUnit.value) return;
    let conversionFactor = 1;
    const conversion = props.unit_conversions.find(
        uc => uc.product_id === selectedProduct.value?.id &&
            uc.from_unit_id === tempDetail.value.unit_id
    );

    if (conversion) {
        conversionFactor = conversion.conversion_factor;
    } else {
        if (selectedProduct.value.unitConversions && selectedProduct.value.unitConversions.length > 0) {
            const productConversion = selectedProduct.value.unitConversions.find(
                uc => uc.from_unit_id === tempDetail.value.unit_id
            );

            if (productConversion) {
                conversionFactor = productConversion.conversion_factor;
            }
        }
    }

    tempDetail.value.base_quantity = tempDetail.value.quantity * conversionFactor;
    tempDetail.value.received_quantity = tempDetail.value.quantity;
    tempDetail.value.received_base_quantity = tempDetail.value.quantity * conversionFactor;
};

const calculateDetailSubtotal = () => {
    tempDetail.value.subtotal = tempDetail.value.quantity * tempDetail.value.price;
};

const handleQuantityChange = () => {
    calculateBaseQuantity();
    calculateDetailSubtotal();
};

const handlePriceChange = () => {
    calculateDetailSubtotal();
};

const addDetail = () => {
    if (!selectedProduct.value || !selectedUnit.value) {
        alert('Produk dan unit harus diisi');
        return;
    }

    const newDetail: StockOutDetail = {
        ...tempDetail.value,
        product: selectedProduct.value,
        unit: selectedUnit.value
    };

    if (!form.value.details) {
        form.value.details = [];
    }

    form.value.details.push(newDetail);
    calculateSubtotal();
    closeModal();
};

const removeDetail = (index: number) => {
    if (!form.value.details) return;

    form.value.details.splice(index, 1);
    calculateSubtotal();
};

const getUnitOptions = (product: Product) => {
    const units: Unit[] = [];

    if (product.defaultUnit) {
        units.push(product.defaultUnit);
    }

    if (product.unitConversions && Array.isArray(product.unitConversions) && product.unitConversions.length > 0) {
        for (const conversion of product.unitConversions) {
            const unit = props.units.find((u: Unit) => u.id === conversion.from_unit_id);

            if (unit) {
                if (!units.some(u => u.id === unit.id)) {
                    units.push(unit);
                }
            }
        }
    } else {
        const productConversions = props.unit_conversions.filter(uc => uc.product_id === product.id);

        if (productConversions.length > 0) {
            for (const conversion of productConversions) {
                if (conversion.from_unit_id) {
                    const fromUnit = props.units.find((u: Unit) => u.id === conversion.from_unit_id);
                    if (fromUnit && !units.some(u => u.id === fromUnit.id)) {
                        units.push(fromUnit);
                    }
                }

                if (conversion.to_unit_id) {
                    const toUnit = props.units.find((u: Unit) => u.id === conversion.to_unit_id);
                    if (toUnit && !units.some(u => u.id === toUnit.id)) {
                        units.push(toUnit);
                    }
                }
            }
        } else {
            if (units.length === 0 && props.units && props.units.length > 0) {
                return props.units;
            }
        }
    }
    return units;
};

// supplier options
const supplierOptions = computed(() => {
    return props.suppliers?.map(supplier => ({
        id: supplier.id,
        name: supplier.name
    })) || [];
});

// status notes options
const statusNotesOptions = computed(() => {
    return props.status_notes?.map(status_note => ({
        id: status_note.label,
        name: status_note.name
    })) || [];
});

const getProductAvailableStock = (product: Product | undefined) => {
    if (!product || !product.currentStocks) return 0;

    return product.currentStocks.reduce((sum, stock) => sum + stock.quantity, 0);
};

function findConversionFactor(): number | null {
    if (!selectedProduct.value || !selectedUnit.value) return null;
    const conversion = props.unit_conversions.find(
        uc => uc.product_id == selectedProduct.value!.id && uc.from_unit_id == selectedUnit.value!.id
    );
    return conversion?.conversion_factor || null;
}

const maxAllowedQuantity = computed(() => {
    const product = selectedProduct.value;
    const unit = selectedUnit.value;

    if (!product) return 99999;

    const availableStock = getProductAvailableStock(product);

    if (unit && product.defaultUnit && unit.id !== product.defaultUnit.id) {
        const conversionFactor = findConversionFactor();
        if (conversionFactor && conversionFactor > 0) {
            return Math.floor(availableStock / conversionFactor);
        }
    }

    return availableStock;
});

const isQuantityExceedingStock = computed(() => {
    return tempDetail.value.quantity > maxAllowedQuantity.value;
});

const submitForm = () => {
    if (!isFormValid.value) {
        return;
    }

    processing.value = true;
    isSubmitting.value = true;
    stockOutStore.editStockOut(props.stock_out.slug || '', form.value);
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

                            <!-- Right: Current date info -->
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-2">
                                    <Calendar class="w-5 h-5 text-blue-600 dark:text-blue-300" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Sekarang</div>
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ tanggalPengeluaranBarang }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <form @submit.prevent="submitForm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Left Column -->
                                <div>
                                    <!-- Stock Out Number -->
                                    <div class="mb-4">
                                        <TextInput v-model="form.stock_out_number" id="stock_out_number"
                                            label="Nomor Pengeluaran" placeholder="Nomor Pengeluaran" readonly />
                                    </div>

                                    <!-- Supplier Select -->
                                    <div class="mb-4">
                                        <VSelect v-model="form.supplier_id" label="Supplier" :options="supplierOptions"
                                            option-label="name" option-value="id" placeholder="Pilih Supplier" />
                                    </div>

                                    <!-- Date -->
                                    <div class="mb-4">
                                        <DateInput v-model="form.out_date" id="out_date" label="Tanggal Pengeluaran"
                                            :required="true" />
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div>
                                    <!-- Status Notes -->
                                    <div class="mb-4">
                                        <VSelect v-model="form.status_notes" label="Status Notes"
                                            :options="statusNotesOptions" option-label="name" option-value="id"
                                            placeholder="Pilih Status" :required="true" />
                                    </div>

                                    <!-- Tax -->
                                    <div class="mb-4">
                                        <TextNumber v-model="form.tax" label="Tax (%)" placeholder="0" id="tax" />
                                    </div>

                                    <!-- Discount -->
                                    <div class="mb-4">
                                        <TextNumber v-model="form.discount" label="Diskon (%)" placeholder="0"
                                            id="discount" />
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-6">
                                <TextAreaInput v-model="form.notes" label="Catatan" id="notes"
                                    placeholder="Tambahkan catatan jika diperlukan" :required="true" />
                            </div>

                            <!-- Products Table -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Daftar Barang</h2>
                                    <BaseButton @click="openAddProductModal" type="button" color="orange"
                                        text="Tambah Barang" />
                                </div>

                                <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Produk
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Unit
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Jumlah
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Harga
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Subtotal
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Catatan
                                                </th>
                                                <th
                                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                    Aksi
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                            <tr v-if="!form.details || form.details.length === 0">
                                                <td colspan="7"
                                                    class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                    Belum ada barang yang ditambahkan
                                                </td>
                                            </tr>
                                            <tr v-for="(detail, index) in form.details" :key="index">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ detail.product?.name }}
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{
                                                        detail.product?.code }}</div>
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ detail.unit?.name }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ detail.quantity }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ formatCurrency(detail.price) }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                    {{ formatCurrency(detail.subtotal) }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                                    {{ detail.notes_detail || '-' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button @click="removeDetail(index)" type="button"
                                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                        <Trash2 class="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div></div>
                                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="text-gray-600 dark:text-gray-300">Subtotal:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(form.subtotal) }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="text-gray-600 dark:text-gray-300">Pajak ({{ form.tax }}%):</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(taxAmount)
                                            }}</span>
                                    </div>
                                    <div class="flex justify-between py-2 text-sm">
                                        <span class="text-gray-600 dark:text-gray-300">Diskon ({{ form.discount
                                            }}%):</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{
                                            formatCurrency(discountAmount) }}</span>
                                    </div>
                                    <div
                                        class="flex justify-between py-2 text-sm border-t border-gray-200 dark:border-gray-700 mt-2 pt-2">
                                        <span class="font-medium text-gray-900 dark:text-white">Total:</span>
                                        <span class="font-bold text-lg text-gray-900 dark:text-white">{{
                                            formatCurrency(form.total_net) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-end space-x-3 pt-4">
                                <Link :href="route('admin.inventory.stock_out.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                        focus:ring-primary-500 focus:ring-offset-2
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                        dark:hover:bg-gray-600 transition-colors">
                                Batal
                                </Link>
                                <BaseButton :disabled="!isFormValid || isSubmitting || stockOutStore.isLoading"
                                    :loading="isSubmitting || stockOutStore.isLoading" text="Ubah Pengeluaran"
                                    loadingText="Mengubah..." color="orange"/>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tambah Barang</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Product Select -->
                    <div>
                        <VSelect v-model="selectedProductId" label="Produk" :options="props.products"
                            option-label="name" placeholder="Pilih Produk" @update:modelValue="handleProductSelect"
                            :required="true">
                            <template #option="{ option }">
                                <div>
                                    <div class="font-medium">{{ option.name }}</div>
                                    <div class="text-xs text-gray-500">{{ option.code }}</div>
                                </div>
                            </template>
                        </VSelect>
                    </div>

                    <div v-if="selectedProduct" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Unit Select -->
                        <div>
                            <VSelect v-model="selectedUnitId" label="Unit" :options="getUnitOptions(selectedProduct)"
                                option-label="name" placeholder="Pilih Unit" @update:modelValue="handleUnitSelect"
                                :required="true" />
                        </div>

                        <!-- Stock Info -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Stok Tersedia
                            </label>
                            <div
                                class="p-2 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                                {{ getProductAvailableStock(selectedProduct) }} {{ selectedProduct.defaultUnit?.name ||
                                    'unit' }}
                            </div>
                        </div>
                    </div>

                    <div v-if="isQuantityExceedingStock"
                        class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 rounded-md border border-blue-200 dark:border-blue-800 flex items-center">
                        <AlertTriangle class="w-5 h-5 mr-2" />
                        <span>Total Pengeluaran Melebihi Stok Tersedia</span>
                    </div>


                    <div v-if="selectedProduct && selectedUnit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Quantity -->
                        <div>
                            <TextNumber v-model="tempDetail.quantity" label="Jumlah" placeholder="0" :min="1"
                                id="quantity" @update:modelValue="handleQuantityChange" :required="true" />
                        </div>

                        <!-- Price -->
                        <div>
                            <TextNumber v-model="tempDetail.price" label="Harga" placeholder="0" :min="0" id="price"
                                @update:modelValue="handlePriceChange" :required="true" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="selectedProduct">
                        <TextAreaInput v-model="tempDetail.notes_detail" label="Catatan Detail"
                            placeholder="Tambahkan catatan " id="notes_detail" :required="true" />
                    </div>

                    <!-- Subtotal Display -->
                    <div v-if="selectedProduct" class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-300">Subtotal:</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{
                                formatCurrency(tempDetail.subtotal) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Batal
                        </button>
                        <BaseButton @click="addDetail" color="blue"
                            :disabled="!selectedProduct || !selectedUnit || tempDetail.quantity <= 0 || isQuantityExceedingStock || tempDetail.notes_detail == ''">
                            Tambahkan
                        </BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
