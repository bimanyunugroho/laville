<!-- StatusBadge.vue -->
<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    label: {
        type: String,
        required: true
    },
    icon: {
        type: Object,
        required: true
    },
    text: {
        type: String,
        default: null
    }
});

const getBadgeClasses = (status) => {
    return {
        'bg-green-100 text-green-800 border border-green-300': status === 'RECEIVED',
        'bg-red-100 text-red-800 border border-red-300': status === 'CANCELED',
        'bg-orange-100 text-orange-800 border border-orange-300': status === 'PROSESS'
    };
};
</script>

<template>
    <div class="flex items-center gap-2">
        <span
            class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
            :class="getBadgeClasses(status)">
            <component :is="icon" class="w-4 h-4 mr-1" />
            {{ label }}: {{ text || status }}
        </span>
    </div>
</template>
