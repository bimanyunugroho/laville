<script setup lang="ts">
import { RefreshCcw, Ban, CheckCheck, Check, ShoppingBag, Store, MapPin, MessageCircle } from 'lucide-vue-next';

const props = defineProps<{
    type: string;
}>();

const bgColor: Record<string, string> = {
    SHOPEE: 'bg-orange-100 text-orange-800',
    TOKOPEDIA: 'bg-blue-100 text-blue-800',
    OFFLINE: 'bg-indigo-100 text-indigo-800',
    INSTAGRAM: 'bg-rose-100 text-rose-800',
    WHATSAPP: 'bg-purple-100 text-purple-800'
};

const labelText: Record<string, string> = {
    SHOPEE: 'Shopee',
    TOKOPEDIA: 'Tokopedia',
    OFFLINE: 'Offline',
    INSTAGRAM: 'Instagram',
    WHATSAPP: 'Whatsapp',
};

const icons: Record<string, any> = {
    SHOPEE: ShoppingBag,
    TOKOPEDIA: Store,
    OFFLINE: MapPin,
    INSTAGRAM: MessageCircle,
    WHATSAPP: MessageCircle
};
</script>

<template>
    <span :class="[
        'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium',
        bgColor[type] ?? 'bg-gray-100 text-gray-800'
    ]">
        <component :is="icons[type]" class="w-3 h-3" v-if="icons[type]" />
        {{ labelText[type] ?? type }}
    </span>
</template>
