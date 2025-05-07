<script setup lang="ts">
defineProps<{
    modelValue: string | number | null;
    options: { id: string | number; name: string }[];
}>();

defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

function getButtonClasses(id: string | number, isActive: boolean): string {
    const base =
        'px-6 py-3 rounded-lg text-sm font-semibold border transition-colors duration-200 min-w-[120px] text-center';

    const variants: Record<string, string> = {
        VALIDATED: isActive ? 'bg-orange-600 text-white border-orange-700' : 'bg-white text-orange-700 border-orange-600 hover:bg-orange-50',
        COMPLETED: isActive ? 'bg-emerald-600 text-white border-emerald-700' : 'bg-white text-emerald-700 border-emerald-600 hover:bg-emerald-50',
        RECEIVED: isActive ? 'bg-green-600 text-white border-green-700' : 'bg-white text-green-700 border-green-600 hover:bg-green-50',
        CANCELED: isActive ? 'bg-red-600 text-white border-red-700' : 'bg-white text-red-700 border-red-600 hover:bg-red-50',
        DEFAULT: isActive ? 'bg-blue-600 text-white border-blue-700' : 'bg-white text-gray-700 border-gray-400 hover:bg-gray-100'
    };

    const variant = typeof id === 'string' ? id.toUpperCase() : 'DEFAULT';

    return `${base} ${variants[variant] || variants.DEFAULT}`;
}
</script>

<template>
    <div class="flex flex-wrap gap-4">
        <button v-for="option in options" :key="option.id" type="button"
            :class="getButtonClasses(option.id, modelValue === option.id)" :disabled="modelValue === option.id"
            @click="$emit('update:modelValue', option.id)">
            {{ option.name }}
        </button>
    </div>
</template>
