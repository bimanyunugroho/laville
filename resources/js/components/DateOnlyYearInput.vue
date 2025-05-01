<script setup lang="ts">
import { ref, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

interface Props {
    modelValue: string | null;
    placeholder?: string;
    required?: boolean;
    error?: string;
    disabled?: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

const selectedDate = ref<any>(
    props.modelValue && !isNaN(parseInt(props.modelValue))
        ? parseInt(props.modelValue)
        : 2025
);

watch(() => props.modelValue, (newVal) => {
    const parsed = parseInt(newVal || '');
    selectedDate.value = !isNaN(parsed) ? parsed : 2025;
});

watch(selectedDate, (newVal) => {
    if (typeof newVal === 'number' && !isNaN(newVal)) {
        emit('update:modelValue', String(newVal));
    } else {
        emit('update:modelValue', '2025');
    }
});
</script>

<template>
    <div class="relative">
        <VueDatePicker v-model="selectedDate" :placeholder="props.placeholder" :required="props.required"
            :disabled="props.disabled" :year-picker="true" auto-apply format="yyyy"
            :class="[
                'block w-full p-2 text-sm text-gray-900 dark:text-white border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500 vue-datepicker',
                props.error ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
            ]" />
    </div>
</template>
