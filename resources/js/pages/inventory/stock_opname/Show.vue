<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Package,
    Info,
    AlertTriangle,
    Truck,
    Calendar,
    User,
    FileText,
    CreditCard,
    Ban,
    CheckCircle,
    CalendarDays,
    ClipboardCheck,
    Search,
    ChevronDown,
    ChevronUp,
    Edit,
    Printer,
    ArrowLeft
} from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '@/helpers/currencyHelper';
import { StockOpname, StockOpnameDetail } from '@/types/stock_opname';
import { formatDate } from '@/helpers/dateStringHelper';
import BadgeStockOpname from '@/components/BadgeStockOpname.vue';

const props = defineProps<{
    title: string;
    desc: string;
    stock_opname: StockOpname;
}>();

// Calculate financial summary
const financialSummary = computed(() => {
    const { subtotal, tax, discount, total_net } = props.stock_opname;
    return {
        subtotal: formatCurrency(subtotal),
        tax: formatCurrency(tax),
        taxPercentage: tax > 0 && subtotal > 0 ? (tax / subtotal * 100).toFixed(1) + '%' : '0%',
        discount: formatCurrency(discount),
        discountPercentage: discount > 0 && subtotal > 0 ? (discount / subtotal * 100).toFixed(1) + '%' : '0%',
        total: formatCurrency(total_net)
    };
});

// Search functionality for details
const searchQuery = ref('');
const sortBy = ref('product.name');
const sortAsc = ref(true);

const filteredDetails = computed(() => {
    if (!props.stock_opname.details) return [];

    let filtered = [...props.stock_opname.details];

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(detail =>
            detail.product?.name.toLowerCase().includes(query) ||
            detail.product?.code.toLowerCase().includes(query) ||
            detail.notes.toLowerCase().includes(query)
        );
    }

    // Apply sorting
    filtered.sort((a, b) => {
        let valA, valB;

        switch (sortBy.value) {
            case 'product.name':
                valA = a.product?.name.toLowerCase() || '';
                valB = b.product?.name.toLowerCase() || '';
                break;
            case 'product.code':
                valA = a.product?.code.toLowerCase() || '';
                valB = b.product?.code.toLowerCase() || '';
                break;
            case 'system_stock':
                valA = a.system_stock;
                valB = b.system_stock;
                break;
            case 'physical_stock':
                valA = a.physical_stock;
                valB = b.physical_stock;
                break;
            case 'difference_stock':
                valA = a.difference_stock;
                valB = b.difference_stock;
                break;
            case 'price':
                valA = a.price;
                valB = b.price;
                break;
            case 'subtotal':
                valA = a.subtotal;
                valB = b.subtotal;
                break;
            default:
                valA = a.product?.name.toLowerCase() || '';
                valB = b.product?.name.toLowerCase() || '';
        }

        if (sortAsc.value) {
            return valA > valB ? 1 : -1;
        } else {
            return valA < valB ? 1 : -1;
        }
    });

    return filtered;
});

const setSort = (field: string) => {
    if (sortBy.value === field) {
        sortAsc.value = !sortAsc.value;
    } else {
        sortBy.value = field;
        sortAsc.value = true;
    }
};

const getSortIcon = (field: string) => {
    if (sortBy.value !== field) return null;
    return sortAsc.value ? ChevronUp : ChevronDown;
};

// Helper to calculate variance percentage
const calculateVariance = (system: number, physical: number) => {
    if (system === 0) return physical === 0 ? '0%' : '∞%';
    const variance = ((physical - system) / system) * 100;
    return variance.toFixed(2) + '%';
};

</script>

<template>
    <AppLayout>

        <Head :title="title" />

        <!-- Non-printable section -->
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

                            <!-- Right: Action Buttons -->
                            <div class="flex flex-wrap items-center gap-3">
                                <Link v-if="stock_opname.status === 'DRAFT'"
                                    :href="route('admin.inventory.stock_opname.edit', stock_opname.slug)" class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                  bg-blue-600 px-4 py-2 text-sm font-medium text-white
                  shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                  focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                <Edit class="h-4 w-4" />
                                Edit Stock Opname
                                </Link>
                                <Link :href="route('admin.inventory.stock_opname.index')" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300
                  bg-white px-4 py-2 text-sm font-medium text-gray-700
                  shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                  focus:ring-primary-500 focus:ring-offset-2
                  dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                  dark:hover:bg-gray-600 transition-colors">
                                <ArrowLeft class="h-4 w-4" />
                                Back to List
                                </Link>
                            </div>
                        </div>

                        <!-- Stock Opname Information -->
                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <ClipboardCheck class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2" />
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Stock Opname Information
                                </h2>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Left Column -->
                                    <div class="space-y-4 col-span-1">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock
                                                Opname Number</h3>
                                            <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">{{
                                                stock_opname.so_number }}</p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</h3>
                                            <div class="mt-1">
                                                <BadgeStockOpname :type="stock_opname.status" />
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <Calendar class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ formatDate(stock_opname.so_date) }}
                                            </p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Period</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <CalendarDays class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ new Date(0, stock_opname.month - 1).toLocaleString('default', {
                                                    month: 'long'
                                                }) }} {{ stock_opname.year }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Middle Column -->
                                    <div class="space-y-4 col-span-1">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <User class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ stock_opname.user?.name || 'N/A' }}
                                            </p>
                                        </div>
                                        <div v-if="stock_opname.status === 'VALIDATED' || stock_opname.user_validator_id !== null">
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Validated
                                                By</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <User class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ stock_opname.userValidator?.name || 'N/A' }}
                                                <span v-if="stock_opname.validator_date"
                                                    class="ml-2 text-xs text-gray-500">
                                                    ({{ formatDate(stock_opname.validator_date) }})
                                                </span>
                                            </p>
                                        </div>
                                        <div v-if="stock_opname.status === 'COMPLETED' || stock_opname.user_ack_id !== null">
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                Verified By</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <User class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ stock_opname.userAck?.name || 'N/A' }}
                                                <span v-if="stock_opname.ack_date" class="ml-2 text-xs text-gray-500">
                                                    ({{ formatDate(stock_opname.ack_date) }})
                                                </span>
                                            </p>
                                        </div>
                                        <div v-if="stock_opname.status === 'CANCELED' || stock_opname.user_reject_id !== null">
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rejected By
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white flex items-center">
                                                <User class="h-4 w-4 mr-1 text-gray-500" />
                                                {{ stock_opname.userReject?.name || 'N/A' }}
                                                <span v-if="stock_opname.reject_date"
                                                    class="ml-2 text-xs text-gray-500">
                                                    ({{ formatDate(stock_opname.reject_date) }})
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Right Column -->
                                    <div class="space-y-4 col-span-1">
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Subtotal
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                                financialSummary.subtotal }}</p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Discount
                                                ({{ financialSummary.discountPercentage }})</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                                financialSummary.discount }}</p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tax ({{
                                                financialSummary.taxPercentage }})</h3>
                                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                                financialSummary.tax }}</p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total NET</h3>
                                            <p :class="{
                                                'text-red-600 dark:text-red-400': parseFloat(financialSummary.total.replace(/[^0-9.-]+/g, '')) < 0,
                                                'text-green-600 dark:text-green-400': parseFloat(financialSummary.total.replace(/[^0-9.-]+/g, '')) > 0,
                                                'text-gray-500 dark:text-gray-400': parseFloat(financialSummary.total.replace(/[^0-9.-]+/g, '')) === 0
                                            }">
                                                {{ parseFloat(financialSummary.total.replace(/[^0-9.-]+/g, '')) > 0 ? '+' + financialSummary.total : (parseFloat(financialSummary.total.replace(/[^0-9.-]+/g, '')) < 0 ? financialSummary.total : financialSummary.total) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mt-6" v-if="stock_opname.notes">
                                    <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</h3>
                                    <div
                                        class="mt-1 p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                        <p class="text-sm text-gray-900 dark:text-white whitespace-pre-line">{{
                                            stock_opname.notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Opname Details -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <Package class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2" />
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Stock Opname Details
                                    </h2>
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                    </div>
                                    <input type="text" v-model="searchQuery" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                           focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5
                           dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                           dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Search products..." />
                                </div>
                            </div>

                            <div class="overflow-x-auto relative">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('product.code')">
                                                <div class="flex items-center">
                                                    Product Code
                                                    <component :is="getSortIcon('product.code')"
                                                        v-if="getSortIcon('product.code')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('product.name')">
                                                <div class="flex items-center">
                                                    Product Name
                                                    <component :is="getSortIcon('product.name')"
                                                        v-if="getSortIcon('product.name')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4">Unit</th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('system_stock')">
                                                <div class="flex items-center">
                                                    System Stock
                                                    <component :is="getSortIcon('system_stock')"
                                                        v-if="getSortIcon('system_stock')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('physical_stock')">
                                                <div class="flex items-center">
                                                    Physical Stock
                                                    <component :is="getSortIcon('physical_stock')"
                                                        v-if="getSortIcon('physical_stock')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('difference_stock')">
                                                <div class="flex items-center">
                                                    Difference
                                                    <component :is="getSortIcon('difference_stock')"
                                                        v-if="getSortIcon('difference_stock')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4">Variance %</th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer" @click="setSort('price')">
                                                <div class="flex items-center">
                                                    Price
                                                    <component :is="getSortIcon('price')" v-if="getSortIcon('price')"
                                                        class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4 cursor-pointer"
                                                @click="setSort('subtotal')">
                                                <div class="flex items-center">
                                                    Subtotal
                                                    <component :is="getSortIcon('subtotal')"
                                                        v-if="getSortIcon('subtotal')" class="ml-1 w-3 h-3" />
                                                </div>
                                            </th>
                                            <th scope="col" class="py-3 px-4">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="detail in filteredDetails" :key="detail.id"
                                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="py-3 px-4 font-medium">{{ detail.product?.code }}</td>
                                            <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">
                                                {{ detail.product?.name }}
                                                <span v-if="detail.product?.variant_name"
                                                    class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ detail.product.variant_name }}
                                                </span>
                                            </td>
                                            <td class="py-3 px-4">{{ detail.unit?.name || 'N/A' }}</td>
                                            <td class="py-3 px-4">{{ formatNumber(detail.system_stock) }}</td>
                                            <td class="py-3 px-4">{{ formatNumber(detail.physical_stock) }}</td>
                                            <td class="py-3 px-4" :class="{
                                                'text-red-600 dark:text-red-400': detail.difference_stock < 0,
                                                'text-green-600 dark:text-green-400': detail.difference_stock > 0,
                                                'text-gray-500 dark:text-gray-400': detail.difference_stock === 0
                                            }">
                                                {{ detail.difference_stock > 0 ? '+' : '' }}{{
                                                    formatNumber(detail.difference_stock) }}
                                            </td>
                                            <td class="py-3 px-4" :class="{
                                                'text-red-600 dark:text-red-400': detail.difference_stock < 0,
                                                'text-green-600 dark:text-green-400': detail.difference_stock > 0,
                                                'text-gray-500 dark:text-gray-400': detail.difference_stock === 0
                                            }">
                                                {{ calculateVariance(detail.system_stock, detail.physical_stock) }}
                                            </td>
                                            <td class="py-3 px-4">{{ formatCurrency(detail.price) }}</td>
                                            <td class="py-3 px-4">{{ formatCurrency(detail.subtotal) }}</td>
                                            <td class="py-3 px-4">
                                                <span v-if="detail.notes" class="inline-block max-w-xs truncate"
                                                    :title="detail.notes">
                                                    {{ detail.notes }}
                                                </span>
                                                <span v-else class="text-gray-400 dark:text-gray-500">-</span>
                                            </td>
                                        </tr>
                                        <tr v-if="!filteredDetails.length" class="bg-white dark:bg-gray-800">
                                            <td colspan="10"
                                                class="py-4 px-4 text-center text-gray-500 dark:text-gray-400">
                                                No stock opname details found.
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="text-xs font-semibold uppercase bg-gray-100 dark:bg-gray-700">
                                        <tr>
                                            <td colspan="3" class="py-3 px-4 text-right text-gray-900 dark:text-white">
                                                Total ({{ filteredDetails.length }} items):
                                            </td>
                                            <td class="py-3 px-4">
                                                {{formatNumber(filteredDetails.reduce((sum, detail) => sum +
                                                    detail.system_stock, 0))}}
                                            </td>
                                            <td class="py-3 px-4">
                                                {{formatNumber(filteredDetails.reduce((sum, detail) => sum +
                                                    detail.physical_stock, 0))}}
                                            </td>
                                            <td class="py-3 px-4" :class="{
                                                'text-red-600 dark:text-red-400': filteredDetails.reduce((sum, detail) => sum + detail.difference_stock, 0) < 0,
                                                'text-green-600 dark:text-green-400': filteredDetails.reduce((sum, detail) => sum + detail.difference_stock, 0) > 0,
                                                'text-gray-500 dark:text-gray-400': filteredDetails.reduce((sum, detail) => sum + detail.difference_stock, 0) === 0
                                            }">
                                                {{filteredDetails.reduce((sum, detail) => sum +
                                                    detail.difference_stock, 0) > 0 ? '+' : ''}}
                                                {{formatNumber(filteredDetails.reduce((sum, detail) => sum +
                                                    detail.difference_stock, 0))}}
                                            </td>
                                            <td class="py-3 px-4"></td>
                                            <td class="py-3 px-4"></td>
                                            <td class="py-3 px-4 text-gray-900 dark:text-white font-bold">
                                                {{formatCurrency(filteredDetails.reduce((sum, detail) => sum +
                                                    detail.subtotal, 0))}}
                                            </td>
                                            <td class="py-3 px-4"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Summary by Product Categories -->
                        <div v-if="stock_opname.details && stock_opname.details.length > 0" class="mb-8">
                            <div class="flex items-center mb-4">
                                <CreditCard class="h-5 w-5 text-gray-500 dark:text-gray-400 mr-2" />
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Stock Discrepancy Summary
                                </h2>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Positive Variance -->
                                <div
                                    class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                                    <h3 class="text-sm font-medium text-green-800 dark:text-green-400 mb-2">
                                        Positive Variance (Excess)
                                    </h3>
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{formatNumber(filteredDetails.filter(d => d.difference_stock > 0)
                                            .reduce((sum, detail) => sum + detail.difference_stock, 0))}}
                                    </div>
                                    <div class="text-sm text-green-600 dark:text-green-400 mt-1">
                                        {{formatCurrency(filteredDetails.filter(d => d.difference_stock > 0)
                                            .reduce((sum, detail) => sum + (detail.difference_stock * detail.price), 0))}}
                                    </div>
                                    <div class="text-xs text-green-500 dark:text-green-500 mt-1">
                                        {{filteredDetails.filter(d => d.difference_stock > 0).length}} items
                                    </div>
                                </div>

                                <!-- Negative Variance -->
                                <div
                                    class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-400 mb-2">
                                        Negative Variance (Shortage)
                                    </h3>
                                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                        {{formatNumber(Math.abs(filteredDetails.filter(d => d.difference_stock < 0)
                                            .reduce((sum, detail) => sum + detail.difference_stock, 0)))}}
                                    </div>
                                    <div class="text-sm text-red-600 dark:text-red-400 mt-1">
                                        {{formatCurrency(Math.abs(filteredDetails.filter(d => d.difference_stock < 0)
                                            .reduce((sum, detail) => sum + (detail.difference_stock * detail.price), 0)))
                                            }}
                                    </div>
                                    <div class="text-xs text-red-500 dark:text-red-500 mt-1">
                                        {{filteredDetails.filter(d => d.difference_stock < 0).length}} items </div>
                                    </div>

                                    <!-- No Variance -->
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                        <h3 class="text-sm font-medium text-gray-800 dark:text-gray-300 mb-2">
                                            No Variance (Matched)
                                        </h3>
                                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">
                                            {{formatNumber(filteredDetails.filter(d => d.difference_stock === 0)
                                                .reduce((sum, detail) => sum + detail.system_stock, 0))}}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{formatCurrency(filteredDetails.filter(d => d.difference_stock === 0)
                                                .reduce((sum, detail) => sum + detail.subtotal, 0))}}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                            {{filteredDetails.filter(d => d.difference_stock === 0).length}} items
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
