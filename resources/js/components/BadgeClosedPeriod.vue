<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  status: string;
}>();

const statusColorMap: Record<string, string> = {
  OPEN: 'bg-orange-100 text-orange-800',
  RUNNING: 'bg-green-100 text-green-800',
  CLOSED: 'bg-red-100 text-red-800'
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
