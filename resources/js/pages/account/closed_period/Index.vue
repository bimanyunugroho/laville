<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Pagination from '@/components/Pagination.vue';
import { CircleCheckBig, FileX, PlusCircle, ShieldClose } from 'lucide-vue-next';
import DateOnlyMonthInput from '@/components/DateOnlyMonthInput.vue';
import DateOnlyYearInput from '@/components/DateOnlyYearInput.vue';
import { getMonthName } from '@/helpers/dateStringHelper';
import { useClosedPeriodStore } from '@/stores/closedPeriodStore';
import { ClosedPeriodClose, ClosedPeriodPageProps } from '@/types/closed_period';
import BadgeClosedPeriod from '@/components/BadgeClosedPeriod.vue';
import BadgeInventory from '@/components/BadgeInventory.vue';
import Swal from 'sweetalert2';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

const props = defineProps<ClosedPeriodPageProps>();
const closedPeriodStore = useClosedPeriodStore();

onMounted(() => {
    closedPeriodStore.initializeFromProps(props);
});

watch(() => props.closed_periods, (newClosedPeriods) => {
    closedPeriodStore.closed_periods = newClosedPeriods;
}, { deep: true });


const toast = useToast();
const formClosedPeriodStore = ref(<ClosedPeriodClose>{
    status_period: 'CLOSED'
});

async function closedPeriodForm(slug: string) {
    const result = await Swal.fire({
        title: 'Perhatian',
        html: 'Apakah Anda Yakin Ingin Menutup Periode Ini ?',
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
            await closedPeriodStore.closedPeriod(
                slug,
                formClosedPeriodStore.value
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
            <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ title }}</h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ desc }}</p>
                            </div>
                            <Link :href="route('admin.account.closed_period.create')"
                                v-if="closedPeriodStore.closed_periods.data.length === 0"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <PlusCircle class="w-4 h-4 mr-2" />
                            Tambah Tutup Periode
                            </Link>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-6">
                            <!-- Search Bar (Sisi Kiri) -->
                            <div class="flex-grow max-w-md">
                                <SearchInput v-model="closedPeriodStore.searchQuery"
                                    placeholder="Cari kode tutup periode..."
                                    @update:modelValue="closedPeriodStore.setSearchQuery" />
                            </div>

                            <!-- Filter Month & Year (Sisi Kanan) -->
                            <div class="flex gap-4">
                                <div class="w-48">
                                    <DateOnlyMonthInput v-model="closedPeriodStore.monthFilter"
                                        placeholder="Filter Bulan"
                                        @update:model-value="(value) => closedPeriodStore.setMonthFilter(value || '')" />
                                </div>
                                <div class="w-48">
                                    <DateOnlyYearInput v-model="closedPeriodStore.yearFilter" placeholder="Filter Tahun"
                                        @update:model-value="(value) => closedPeriodStore.setYearFilter(value || '')" />
                                </div>
                            </div>
                        </div>


                        <!-- Loading Indicator -->
                        <div v-if="closedPeriodStore.isLoading" class="text-center py-12">
                            <div
                                class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <p class="mt-3 text-gray-700 dark:text-gray-300">Memuat data tutup periode...</p>
                        </div>

                        <!-- Table -->
                        <div v-else class="overflow-x-auto">
                            <Table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-200">
                                <template #header>
                                    <tr class="bg-gray-100 dark:bg-gray-200 text-gray-700 dark:text-gray-900">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            No.Tutup Periode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Tahun</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Bulan</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status Periode</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">
                                            Status Konfirmasi</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                                            Aksi</th>
                                    </tr>
                                </template>
                                <template #body>
                                    <tr v-if="closedPeriodStore.closed_periods.data.length === 0">
                                        <td colspan="11" class="px-4 py-10 text-center">
                                            <div
                                                class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                                <FileX class="w-12 h-12 text-gray-400 dark:text-gray-500" />
                                                <p class="text-base">Tidak ada data tutup periode yang ditemukan.</p>
                                                <button @click="closedPeriodStore.setSearchQuery('')"
                                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                    Muat Ulang
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="(closed_period, index) in closedPeriodStore.closed_periods.data"
                                        :key="closed_period.slug"
                                        class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                            {{ index + 1 + (closedPeriodStore.closed_periods.current_page - 1) *
                                                closedPeriodStore.closed_periods.per_page }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <span class="font-medium">{{ closed_period.no_closed }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ closed_period.year || ''
                                                }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{
                                                    getMonthName(closed_period.month) || '' }}</span>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            <BadgeClosedPeriod :status="closed_period.status_period" />
                                        </td>
                                        <td
                                            class="px-4 py-3 whitespace-nowrap text-sm text-left text-gray-700 dark:text-gray-200">
                                            <BadgeInventory :type="closed_period.status_confirm" />
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                            <div class="flex justify-center gap-1">
                                                <Link v-if="closed_period.user_ack_id === null"
                                                    :href="route('admin.account.closed_period.approval.view', closed_period.slug)"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-green-600 hover:text-white hover:bg-green-600 dark:text-green-400 dark:hover:text-white dark:hover:bg-green-500 transition-colors duration-150">
                                                <CircleCheckBig class="w-4 h-4" />
                                                </Link>
                                            </div>
                                            <div class="flex justify-center gap-1">
                                                <button v-if="closed_period.user_ack_id !== null"
                                                    @click="closedPeriodForm(closed_period.slug ?? '')"
                                                    class="inline-flex items-center justify-center px-2 py-1 rounded-md text-sm font-medium text-rose-600 hover:text-white hover:bg-rose-600 dark:text-rose-400 dark:hover:text-white dark:hover:bg-rose-500 transition-colors duration-150">
                                                    <ShieldClose class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </Table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            <Pagination :links="closedPeriodStore.closed_periods.links"
                                :current-page="closedPeriodStore.closed_periods.current_page"
                                :per-page="closedPeriodStore.closed_periods.per_page"
                                :total="closedPeriodStore.closed_periods.total" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
