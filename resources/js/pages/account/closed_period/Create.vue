<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TextInput from '@/components/TextInput.vue';
import BaseButton from '@/components/BaseButton.vue';
import { useClosedPeriodStore } from '@/stores/closedPeriodStore';
import DateOnlyMonthInput from '@/components/DateOnlyMonthInput.vue';
import DateOnlyYearInput from '@/components/DateOnlyYearInput.vue';

const props = defineProps<{
    title: string;
    desc: string;
    no_closed: string;
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const closedPeriodStore = useClosedPeriodStore();

const newClosedPeriod = ref({
    no_closed: props.no_closed,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    user_id: props.currentUser?.id,
    closed_date: new Date().toISOString().split('T')[0],
    user_ack_id: null,
    ack_date: '',
    user_reject_id: null,
    reject_date: '',
    status_period: 'OPEN',
    status_confirm: 'PROSESS',
    status: true
});

// Handler untuk submit form
const handleSubmit = () => {
    // Pastikan data lengkap sebelum menyimpan
    if (!newClosedPeriod.value.month || !newClosedPeriod.value.year) {
        alert('Silakan lengkapi data bulan dan tahun');
        return;
    }

    // Panggil action store untuk menyimpan data
    closedPeriodStore.createClosedPeriode(newClosedPeriod.value);
};
</script>

<template>
    <AppLayout>

        <Head :title="title" />

        <div class="py-6">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg h-[550px] flex flex-col justify-between">
                        <div class="p-6">
                            <div class="mb-6">
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ title }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ desc }}
                                </p>
                            </div>

                            <TextInput id="no_closed" v-model="newClosedPeriod.no_closed" label="No. Tutup Periode"
                                placeholder="Tutup Periode" required readonly />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <DateOnlyMonthInput :model-value="newClosedPeriod.month.toString()"
                                        label="Bulan Tutup Periode" placeholder="Pilih Bulan" required />
                                </div>
                                <div>
                                    <DateOnlyYearInput :model-value="newClosedPeriod.year.toString()"
                                        label="Tahun Tutup Periode" placeholder="Pilih Tahun" required />
                                </div>
                            </div>

                            <!-- Tombol dipindahkan ke bawah -->
                        </div>

                        <!-- Tombol di bagian bawah -->
                        <div
                            class="p-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end space-x-3">
                            <Link :href="route('admin.account.closed_period.index')" class="inline-flex justify-center rounded-md border border-gray-300
                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                        focus:ring-primary-500 focus:ring-offset-2
                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                   dark:hover:bg-gray-600 transition-colors">
                            Batal
                            </Link>
                            <BaseButton :loading="closedPeriodStore.isLoading" text="Simpan" loadingText="Menyimpan..."
                                color="blue" type="submit" />
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </AppLayout>
</template>
