<script setup lang="ts">
const props = defineProps<{
    type: 'success' | 'danger' | 'warning' | 'info';
    text: string;
}>();

const bgColor = {
    success: 'bg-green-100 text-green-800',
    danger: 'bg-red-100 text-red-800',
    warning: 'bg-yellow-100 text-yellow-800',
    info: 'bg-blue-100 text-blue-800',
};
</script>

<template>
    <span :class="[
        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
        bgColor[type]
    ]">
        {{ text }}
    </span>
</template>
