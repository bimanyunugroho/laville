<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { UnitPageProps } from '@/types/unit';
import { useUnitStore } from '@/stores/unitStore';
import AppLayout from '@/layouts/AppLayout.vue';
import SearchInput from '@/components/SearchInput.vue';
import Table from '@/components/Table.vue';
import Badge from '@/components/Badge.vue';
import Pagination from '@/components/Pagination.vue';
import { Pencil, Trash2, FileX } from 'lucide-vue-next';

const props = defineProps<UnitPageProps>();
const unitStore = useUnitStore();
onMounted(() => {
    unitStore.initializeFromProps(props);
});

watch(() => props.units, (newUnits) => {
    unitStore.units = newUnits;
}, { deep: true });
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
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-3">
                            <!-- Search Bar -->
                            <div class="flex-grow">
                                <SearchInput v-model="unitStore.searchQuery"
                                    placeholder="Cari berdasarkan kode atau nama satuan..."
                                    @update:modelValue="unitStore.setSearchQuery" />
                            </div>

                            <Link :href="route('admin.master.unit.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Tambah Satuan
                            </Link>
                        </div>


                        <!-- Loading Indicator -->
                        <div v-if="unitStore.isLoading" class="text-center py-4">
                            <div
                                class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-solid border-current border-r-transparent align-text-bottom text-indigo-600">
                            </div>
                            <span class="ml-2 text-gray-900 dark:text-gray-100">Memuat data...</span>
                        </div>

                        <!-- Table -->
                        <Table v-else class="min-w-full bg-white shadow-md rounded-lg overflow-hidden dark:bg-gray-900">
                            <template #header>
                                <tr class="text-gray-900 dark:text-gray-800">
                                    <th class="px-4 py-2 text-left text-sm font-semibold">No</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold">Nama</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold">Kode</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold">Status</th>
                                    <th class="px-4 py-2 text-right text-sm font-semibold">Aksi</th>
                                </tr>
                            </template>
                            <template #body>
                                <tr v-if="unitStore.units.data.length === 0">
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div
                                            class="flex flex-col items-center justify-center gap-4 text-gray-500 dark:text-gray-400">
                                            <FileX class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                                            <p class="text-sm">Tidak ada data satuan yang ditemukan.</p>
                                            <button @click="unitStore.setSearchQuery('')"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                                Muat Ulang
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-for="(unit, index) in unitStore.units.data" :key="unit.slug"
                                    class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-4 py-1.5 text-gray-900 dark:text-gray-100">{{ index + 1 +
                                        (unitStore.units.current_page - 1) * unitStore.units.per_page }}</td>
                                    <td class="px-4 py-1.5 text-gray-900 dark:text-gray-100">{{ unit.name }}</td>
                                    <td class="px-4 py-1.5 text-gray-900 dark:text-gray-100">{{ unit.code }}</td>
                                    <td class="px-4 py-1.5">
                                        <Badge :type="unit.is_active ? 'success' : 'danger'"
                                            :text="unit.is_active ? 'Aktif' : 'Nonaktif'" />
                                    </td>
                                    <td class="px-4 py-1.5 text-right">
                                        <div class="flex justify-end gap-2">
                                            <Link :href="route('admin.master.unit.edit', unit.slug)"
                                                class="inline-flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium text-indigo-600 hover:text-white hover:bg-indigo-600 dark:text-indigo-400 dark:hover:text-white dark:hover:bg-indigo-500 transition-colors duration-150">
                                            <Pencil class="w-4 h-4" />
                                            <span>Edit</span>
                                            </Link>

                                            <button @click="unitStore.deleteUnit(unit)" :disabled="unitStore.isLoading"
                                                class="inline-flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:text-white hover:bg-red-600 dark:text-red-400 dark:hover:text-white dark:hover:bg-red-500 transition-colors duration-150 disabled:opacity-50 cursor-pointer">
                                                <Trash2 class="w-4 h-4" />
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </Table>

                        <!-- Pagination -->
                        <Pagination :links="unitStore.units.links" :current-page="unitStore.units.current_page"
                            :per-page="unitStore.units.per_page" :total="unitStore.units.total" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
