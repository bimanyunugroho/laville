<script setup lang="ts">
import { RefreshCcw, Ban, CheckCheck } from 'lucide-vue-next';

const props = defineProps<{
    type: string;
}>();

const bgColor: Record<string, string> = {
    RECEIVED: 'bg-green-100 text-green-800',
    CANCELED: 'bg-red-100 text-red-800',
    PROSESS: 'bg-yellow-100 text-yellow-800',
};

const labelText: Record<string, string> = {
    RECEIVED: 'Diterima',
    CANCELED: 'Dibatalkan',
    PROSESS: 'Diproses',
};

const icons: Record<string, any> = {
    RECEIVED: CheckCheck,
    CANCELED: Ban,
    PROSESS: RefreshCcw,
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
