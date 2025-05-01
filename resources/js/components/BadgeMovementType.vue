<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: string;
}>();

const statusColorMap: Record<string, string> = {
  MASTER_BARU: 'bg-blue-100 text-blue-800',
  PROSESS: 'bg-orange-100 text-orange-800',
  MASUK: 'bg-green-100 text-green-800',
  KELUAR: 'bg-red-100 text-red-800',
};

const badgeClass = computed(() => statusColorMap[props.status] || 'bg-gray-100 text-gray-800');

const formattedStatus = computed(() =>
  props.status.replaceAll('_', ' ').toLowerCase().replace(/^\w/, c => c.toUpperCase())
);
</script>

<template>
  <span
    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
    :class="badgeClass"
  >
    {{ formattedStatus }}
  </span>
</template>
