<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: string;
}>();

const statusColorMap: Record<string, string> = {
  MASTER_BARU: 'bg-blue-100 text-blue-800',
  PEMBELIAN_BARANG: 'bg-orange-100 text-orange-800',
  PENERIMAAN_BARANG: 'bg-green-100 text-green-800',
  STOCK_OPNAME: 'bg-purple-100 text-purple-800',
  PENJUALAN: 'bg-amber-100 text-amber-800',
};

const badgeClass = computed(() => statusColorMap[props.status] || 'bg-gray-100 text-gray-800');

const formattedStatus = computed(() =>
  props.status.replaceAll('_', ' ').toLowerCase().replace(/^\w/, c => c.toUpperCase())
);
</script>

<template>
  <span
    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-"
    :class="badgeClass"
  >
    {{ formattedStatus }}
  </span>
</template>

