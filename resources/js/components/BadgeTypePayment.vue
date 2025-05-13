<script setup lang="ts">
import { RefreshCcw, Ban, CheckCheck, Check, Banknote, CreditCard, Wallet, Store } from 'lucide-vue-next';

const props = defineProps<{
    type: string;
}>();

const bgColor: Record<string, string> = {
    CASH: 'bg-red-100 text-red-800',
    DEBIT: 'bg-green-100 text-green-800',
    CREDIT: 'bg-orange-100 text-orange-800',
    WALLET: 'bg-yellow-100 text-yellow-800',
    TRANSFER: 'bg-yellow-100 text-yellow-800',
    MARKETPLACE: 'bg-yellow-100 text-yellow-800',
};

const labelText: Record<string, string> = {
    CASH: 'Cash',
    DEBIT: 'Debit',
    CREDIT: 'Kredit',
    WALLET: 'Wallet',
    TRANSFER: 'Transfer',
    MARKETPLACE: 'Marketplace',
};

const icons: Record<string, any> = {
    CASH: Banknote,
    DEBIT: CreditCard,
    CREDIT: Ban,
    WALLET: Wallet,
    TRANSFER: Banknote,
    MARKETPLACE: Store,
}
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
