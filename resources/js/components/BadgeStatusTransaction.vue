<script setup lang="ts">
import { RefreshCcw, Ban, CheckCheck, Check, CircleDollarSign, Clock, CheckCircle } from 'lucide-vue-next';

const props = defineProps<{
    type: string;
}>();

const bgColor: Record<string, string> = {
    PAID: 'bg-emerald-100 text-emerald-800',
    PARTIAL: 'bg-orange-100 text-orange-800',
    PENDING: 'bg-red-100 text-red-800',
};

const labelText: Record<string, string> = {
    PARTIAL: 'Sebagian Dibayar',
    PENDING: 'Belum Dibayar',
    PAID: 'Lunas'
};

const icons: Record<string, any> = {
    PARTIAL: CircleDollarSign,
    PENDING: Clock,
    PAID: CheckCircle
};

</script>

<template>
    <span :class="[
        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium',
        bgColor[type] ?? 'bg-gray-100 text-gray-800'
    ]">
        <component
            :is="icons[type]"
            class="w-3 h-3"
            v-if="icons[type]"
        />
        {{ labelText[type] ?? type }}
    </span>
</template>
