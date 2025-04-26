<script setup lang="ts">
interface Props {
    modelValue: boolean;
    id: string;
    label: string;
}

defineProps<Props>();
defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();
</script>

<template>
    <div class="flex items-center">
        <input :id="id" type="checkbox" :checked="modelValue"
            @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)" class="h-4 w-4 rounded border-gray-300 text-primary-600
                   focus:ring-primary-500 dark:border-gray-600
                   dark:focus:ring-primary-600" />
        <label :for="id" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
            {{ label }}
        </label>
    </div>
</template>
