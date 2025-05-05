<script setup lang="ts">
import { ShieldOff, Building2, ExternalLink } from 'lucide-vue-next';

const props = defineProps<{
    type: string;
}>();

const bgColor: Record<string, string> = {
    BARANG_RUSAK: 'bg-red-100 text-red-800',
    PEMAKAIAN_INTERNAL: 'bg-orange-100 text-orange-800',
    PEMAKAIAN_EKSTERNAL: 'bg-purple-100 text-purple-800',
};

const labelText: Record<string, string> = {
    BARANG_RUSAK: 'Barang Rusak',
    PEMAKAIAN_INTERNAL: 'Pemakaian Internal',
    PEMAKAIAN_EKSTERNAL: 'Pemakaian Eksternal',
};

const icons: Record<string, any> = {
    BARANG_RUSAK: ShieldOff,
    PEMAKAIAN_INTERNAL: Building2,
    PEMAKAIAN_EKSTERNAL: ExternalLink,
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
