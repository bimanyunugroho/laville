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
    props.modelValue
        ? { month: parseInt(props.modelValue) - 1, year: 2025 }
        : null
);

watch(() => props.modelValue, (newVal) => {
    selectedDate.value = newVal
        ? { month: parseInt(newVal) - 1, year: 2025 }
        : null;
});

watch(selectedDate, (newVal) => {
    if (!newVal || typeof newVal.month !== 'number') {
        emit('update:modelValue', null);
        return;
    }

    const monthStr = String(newVal.month + 1).padStart(2, '0');
    emit('update:modelValue', monthStr);
});
</script>

<template>
    <div class="relative">
        <VueDatePicker
            v-model="selectedDate"
            :placeholder="props.placeholder"
            :required="props.required"
            :disabled="props.disabled"
            :month-picker="true"
            auto-apply
            format="MMMM"
            :min-year="2025"
            :enable-year-select="false"
            :year-picker="false"
            :class="[
                'block w-full p-2 text-sm text-gray-900 dark:text-white border rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500 vue-datepicker',
                props.error ? 'border-red-500 dark:border-red-500' : 'border-gray-300 dark:border-gray-600'
            ]"
        />
    </div>
</template>
<style scoped>
:deep(.dp__selection_grid_header .dp--year-mode-picker) {
    display: none !important;
}
</style>
