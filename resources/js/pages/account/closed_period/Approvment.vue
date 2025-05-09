<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { User2, Calendar, User, ArrowLeftCircle, Info, CheckCircle, XCircle, Clock } from 'lucide-vue-next';
import { ClosedPeriod, ClosedPeriodApproval } from '@/types/closed_period';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
import { useClosedPeriodStore } from '@/stores/closedPeriodStore';
import { StatusPoOption } from '@/types/status_po';
import VButtonSelect from '@/components/VButtonSelect.vue';
import BaseButton from '@/components/BaseButton.vue';

const props = defineProps<{
    title: string;
    desc: string;
    closed_period: ClosedPeriod;
    closed_period_approval: ClosedPeriodApproval;
    status: StatusPoOption[];
    currentUser: {
        id: number;
        name: string;
    } | null;
}>();

const toast = useToast();
const closedPeriodStore = useClosedPeriodStore();

const formClosedPeriodStore = ref(<ClosedPeriodApproval>{
    user_ack_id: 0 || null,
    ack_date: null,
    user_reject_id: 0 || null,
    reject_date: null,
    status_period: null,
    status_confirm: null
});

const statusOptions = computed(() => {
    return props.status
        ?.filter(status => status.label !== 'PROSESS')
        .map(status => ({
            id: status.label,
            name: status.name
        })) || [];
});


watch(() => formClosedPeriodStore.value.status_confirm, (newStatusId) => {
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    if (!props.currentUser) return;

    if (newStatusId === 'RECEIVED') {
        formClosedPeriodStore.value.user_ack_id = props.currentUser.id;
        formClosedPeriodStore.value.ack_date = now;
        formClosedPeriodStore.value.user_reject_id = null;
        formClosedPeriodStore.value.reject_date = null;
        formClosedPeriodStore.value.status_period = 'RUNNING';
    } else if (newStatusId === 'CANCELED') {
        formClosedPeriodStore.value.user_reject_id = props.currentUser.id;
        formClosedPeriodStore.value.reject_date = now;
        formClosedPeriodStore.value.user_ack_id = null;
        formClosedPeriodStore.value.ack_date = null;
        formClosedPeriodStore.value.status_period = 'OPEN';
    } else {
        formClosedPeriodStore.value.user_ack_id = null;
        formClosedPeriodStore.value.ack_date = null;
        formClosedPeriodStore.value.user_reject_id = null;
        formClosedPeriodStore.value.reject_date = null;
        formClosedPeriodStore.value.status_period = 'OPEN';
    }
});
async function approvalForm() {
    const result = await Swal.fire({
        title: 'Perhatian',
        html: 'Apakah Periode Ini Sudah Sesuai ?',
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
            await closedPeriodStore.approvalClosedPeriod(
                props.closed_period_approval.slug,
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
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <!-- Header -->
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ desc }}
                            </p>
                        </div>

                        <!-- Detail Content -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-700 dark:text-gray-300">
                            <div>
                                <span class="font-semibold">No Tutup Periode:</span>
                                <div>{{ closed_period.no_closed }}</div>
                            </div>
                            <div>
                                <span class="font-semibold">Bulan:</span>
                                <div>{{ closed_period.month }}</div>
                            </div>
                            <div>
                                <span class="font-semibold">Tahun:</span>
                                <div>{{ closed_period.year }}</div>
                            </div>
                            <div>
                                <span class="font-semibold">Tanggal Dibuat:</span>
                                <div>{{ closed_period.closed_date }}</div>
                            </div>
                            <div>
                                <span class="font-semibold">Status:</span>
                                <div>{{ closed_period.status_period }}</div>
                            </div>
                            <div>
                                <span class="font-semibold">Dibuat oleh:</span>
                                <div>{{ closed_period.user?.name ?? '-' }}</div>
                            </div>
                            <div v-if="closed_period.user_ack_id">
                                <span class="font-semibold">Disetujui oleh:</span>
                                <div>{{ closed_period.userAck?.name ?? '-' }}</div>
                            </div>
                            <div v-if="closed_period.ack_date">
                                <span class="font-semibold">Tanggal Disetujui:</span>
                                <div>{{ closed_period.ack_date }}</div>
                            </div>
                            <div v-if="closed_period.user_reject_id">
                                <span class="font-semibold">Ditolak oleh:</span>
                                <div>{{ closed_period.userReject?.name ?? '-' }}</div>
                            </div>
                            <div v-if="closed_period.reject_date">
                                <span class="font-semibold">Tanggal Ditolak:</span>
                                <div>{{ closed_period.reject_date }}</div>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div>
                            <form @submit.prevent="approvalForm">
                                <div class="">
                                    <div
                                        class="space-y-4 dark:bg-gray-700 bg-white p-5 rounded-lg shadow-md border border-gray-200 dark:border-gray-600">
                                        <!-- Header -->
                                        <div class="border-b border-gray-200 dark:border-gray-600 pb-4 mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                Form Persetujuan Setting Periode
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                Silakan pilih status persetujuan untuk dokumen ini
                                            </p>
                                        </div>

                                        <!-- Status Selection dengan tampilan yang lebih menarik -->
                                        <div class="mb-4">
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Status Persetujuan:
                                            </label>
                                            <VButtonSelect v-model="formClosedPeriodStore.status_confirm"
                                                :options="statusOptions"
                                                class="flex space-x-2 rounded-md overflow-hidden" />
                                        </div>

                                        <!-- Status Display dengan visualisasi yang lebih jelas -->
                                        <div v-if="formClosedPeriodStore.status_confirm"
                                            class="space-y-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-800 border border-green-300': formClosedPeriodStore.status_confirm === 'RECEIVED',
                                                        'bg-red-100 text-red-800 border border-red-300': formClosedPeriodStore.status_confirm === 'CANCELED',
                                                        'bg-blue-100 text-blue-800 border border-blue-300': formClosedPeriodStore.status_confirm !== 'RECEIVED' && formClosedPeriodStore.status_confirm !== 'CANCELED',
                                                    }">
                                                    <template v-if="formClosedPeriodStore.status_confirm === 'RECEIVED'">
                                                        <CheckCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template
                                                        v-else-if="formClosedPeriodStore.status_confirm === 'CANCELED'">
                                                        <XCircle class="w-4 h-4 mr-1" />
                                                    </template>
                                                    <template v-else>
                                                        <Clock class="w-4 h-4 mr-1" />
                                                    </template>
                                                    Status: {{ formClosedPeriodStore.status_confirm }}
                                                </span>
                                            </div>

                                            <!-- Approval Information -->
                                            <div v-if="formClosedPeriodStore.user_ack_id && formClosedPeriodStore.ack_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                    <Calendar class="w-5 h-5 text-green-500 dark:text-green-400" />
                                                </div>
                                                <div class="text-sm text-green-700 dark:text-green-400">
                                                    <div><span class="font-medium">Disetujui</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formClosedPeriodStore.ack_date }}
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Rejection Information -->
                                            <div v-if="formClosedPeriodStore.user_reject_id && formClosedPeriodStore.reject_date"
                                                class="flex items-center gap-3 p-4 rounded-lg border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 w-fit">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <User2 class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                    <Calendar class="w-5 h-5 text-red-500 dark:text-red-400" />
                                                </div>
                                                <div class="text-sm text-red-700 dark:text-red-400">
                                                    <div><span class="font-medium">Dibatalkan</span> oleh <strong>{{
                                                        props.currentUser?.name }}</strong></div>
                                                    <div class="italic">{{ formClosedPeriodStore.reject_date }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Default Message yang muncul ketika belum ada pilihan -->
                                        <div v-if="!formClosedPeriodStore.status_confirm"
                                            class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg my-4">
                                            <div class="flex items-start gap-3">
                                                <Info class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5" />
                                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                                    <p class="font-medium">Pilih tindakan Setting Periode</p>
                                                    <p class="mt-1">Silakan pilih status persetujuan untuk
                                                        melanjutkan
                                                        proses Setting Periode</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Buttons with enhanced styling -->
                                        <div
                                            class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-600 mt-4">
                                            <Link :href="route('admin.inventory.purchase_order.index')" class="inline-flex items-center justify-center rounded-md border border-gray-300
                                        bg-white px-4 py-2 text-sm font-medium text-gray-700
                                        shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2
                                        focus:ring-primary-500 focus:ring-offset-2
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300
                                        dark:hover:bg-gray-600 transition-colors">
                                            <ArrowLeftCircle class="w-4 h-4 mr-2" />
                                            Kembali
                                            </Link>

                                            <BaseButton :loading="closedPeriodStore.isLoading"
                                                :text="formClosedPeriodStore.status_confirm === 'RECEIVED' ? 'Setujui' : formClosedPeriodStore.status_confirm === 'CANCELED' ? 'REJECTED' : 'APPROVED'"
                                                loadingText="Proses Approval..."
                                                :color="formClosedPeriodStore.status_confirm === 'RECEIVED' ? 'emerald' : formClosedPeriodStore.status_confirm === 'CANCELED' ? 'red' : 'blue'"
                                                :disabled="!formClosedPeriodStore.status_confirm"
                                                class="flex items-center">
                                                <template #icon>
                                                    <template v-if="formClosedPeriodStore.status_confirm === 'PROSESS'">
                                                        <CheckCircle class="w-4 h-4 mr-2" />
                                                    </template>
                                                    <template
                                                        v-else-if="formClosedPeriodStore.status_confirm === 'PROSESS'">
                                                        <XCircle class="w-4 h-4 mr-2" />
                                                    </template>
                                                </template>
                                            </BaseButton>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
