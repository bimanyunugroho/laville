<script setup lang="ts">
import { ref, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

interface Props {
    modelValue: string | Date | null;
    id: string;
    label?: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: string | Date | null): void;
}>();

// Function to format Date to YYYY-MM-DD
const formatDate = (date: Date | string | null): string | null => {
    if (!date) return null;

    const d = new Date(date);
    const year = d.getFullYear();
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const day = d.getDate().toString().padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const selectedDate = ref<string | Date | null>(props.modelValue);

// Update selectedDate when props.modelValue changes
watch(() => props.modelValue, (newVal) => {
    selectedDate.value = newVal;
});

// Emit updated date to parent in YYYY-MM-DD format
watch(selectedDate, (newVal) => {
    emit('update:modelValue', formatDate(newVal));
});
</script>

<template>
    <div class="relative">
        <label :for="props.id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            {{ props.label }}
            <span v-if="props.required" class="text-red-500">*</span>
        </label>

        <VueDatePicker
            :id="props.id"
            v-model="selectedDate"
            :placeholder="props.placeholder"
            :required="props.required"
            :disabled="props.disabled"
            format="dd/MM/yyyy"
            :enable-time-picker="false"
            auto-apply
            :class="[
                'block w-full p-2 text-sm text-gray-900 dark:text-white border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500 vue-datepicker',
                props.error ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
            ]"
        />

        <div v-if="props.error" class="mt-1 text-sm text-red-500">
            {{ props.error }}
        </div>
    </div>
</template>
