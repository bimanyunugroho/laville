<script setup lang="ts">
interface Props {
    modelValue: string | null;
    id: string;
    label: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
    rows?: number;
    cols?: number;
    maxlength?: number;
}

defineProps<Props>();
defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();
</script>

<template>
    <div>
        <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="mt-1">
            <textarea
                :id="id"
                :value="modelValue"
                @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                :required="required"
                :placeholder="placeholder"
                :rows="rows || 4"
                :cols="cols"
                :maxlength="maxlength"
                class="block w-full p-2 text-sm text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
            ></textarea>
            <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        </div>
    </div>
</template>
