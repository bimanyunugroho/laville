<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import CheckboxInput from '@/components/CheckboxInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import TextNumber from '@/components/TextNumber.vue';
import TextAreaInput from '@/components/TextAreaInput.vue';
import VSelect from '@/components/VSelect.vue';
import { Unit } from '@/types/unit';
import { Product } from '@/types/produk';
import { useUnitConversionStore } from '@/stores/unitConversionStore';
import Swal from 'sweetalert2';
import { UnitConversion } from '@/types/unit_conversion';
import { formatNumber } from '@/helpers/currencyHelper';
import { Package, ArrowRight, Calendar, Clipboard, Calculator } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    desc: string;
    unit_conversion: UnitConversion;
}>();

const unitConversionStore = useUnitConversionStore();
</script>

<template>
    <AppLayout>

        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ title }}
                                    </h1>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ desc }}
                                    </p>
                                </div>

                                <div class="hidden sm:block">
                                    <div class="flex items-center space-x-3">
                                        <Link :href="route('admin.master.unit_conversion.edit', unit_conversion.slug)"
                                            class="inline-flex items-center justify-center gap-2 rounded-md border border-transparent
                                        bg-blue-600 px-4 py-2 text-sm font-medium text-white
                                        shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2
                                        focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                        Edit Konversi Satuan
                                        </Link>
                                        <Link :href="route('admin.master.unit_conversion.index')" class="inline-flex justify-center rounded-md border border-gray-300
                                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                        focus:ring-primary-500 focus:ring-offset-2
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                        dark:hover:bg-gray-600 transition-colors">
                                        Kembali
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Product Information Card -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <Package class="w-5 h-5 text-blue-500" />
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Informasi Produk</h2>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Produk</p>
                                        <p class="mt-1 text-base font-medium text-gray-900 dark:text-white">{{
                                            unit_conversion.product?.code || '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Produk</p>
                                        <p class="mt-1 text-base font-medium text-gray-900 dark:text-white">{{
                                            unit_conversion.product?.name || '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Satuan Dasar</p>
                                        <p
                                            class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                            {{ unit_conversion.product?.defaultUnit?.name || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Conversion Details Card -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <Calculator class="w-5 h-5 text-green-500" />
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Detail Konversi</h2>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Konversi Satuan
                                        </p>
                                        <div class="mt-2 flex items-center space-x-3">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                {{ unit_conversion.fromUnit?.name || '-' }}
                                            </span>
                                            <ArrowRight class="w-4 h-4 text-gray-400" />
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                                {{ unit_conversion.toUnit?.name || '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nilai Konversi
                                        </p>
                                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{
                                            formatNumber(unit_conversion.conversion_factor) || '-' }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Contoh Konversi
                                        </p>
                                        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                                            1 {{ unit_conversion.fromUnit?.name || '-' }} = {{
                                                formatNumber(unit_conversion.conversion_factor) || '-' }} {{
                                            unit_conversion.toUnit?.name || '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta Information Card -->
                            <div
                                class="bg-white dark:bg-gray-900 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3 mb-4">
                                    <Clipboard class="w-5 h-5 text-purple-500" />
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">Metadata</h2>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat Pada</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                            unit_conversion.created_at }}</p>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Terakhir
                                            Diperbarui</p>
                                        <p class="mt-1 text-sm text-gray-900 dark:text-white">{{
                                            unit_conversion.updated_at }}</p>
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
