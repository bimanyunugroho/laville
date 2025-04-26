<script setup lang="ts">
interface Props {
    modelValue: string | number; // Accept both string and number
    id: string;
    label: string;
    placeholder?: string;
    required?: boolean;
    error?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

// Format number with thousand separators (dots)
function formatNumber(value: string | number): string {
    if (value === null || value === undefined) return '';

    // Convert to string if it's a number
    const stringValue = String(value);

    // Remove all non-digit characters
    const cleanValue = stringValue.replace(/\D/g, '');

    // Format with thousand separators
    if (cleanValue === '') return '';

    return new Intl.NumberFormat('id-ID', {
        useGrouping: true,
        maximumFractionDigits: 0
    }).format(Number(cleanValue));
}

// Handle input changes
function handleInput(event: Event) {
    const input = event.target as HTMLInputElement;
    const cursorPosition = input.selectionStart || 0;
    const oldValue = input.value;
    const oldLength = oldValue.length;

    // Only allow digits
    const digitsOnly = input.value.replace(/[^\d]/g, '');

    // Format the value
    const formattedValue = formatNumber(digitsOnly);
    input.value = formattedValue;

    // Adjust cursor position to account for formatting
    const newLength = formattedValue.length;
    const newPosition = cursorPosition + (newLength - oldLength);

    // Set cursor position after Vue updates the DOM
    setTimeout(() => {
        input.setSelectionRange(newPosition, newPosition);
    }, 0);

    // Emit the raw value (without separators) for v-model binding
    emit('update:modelValue', digitsOnly);
}

// Keep the displayed value formatted
function ensureFormattedValue() {
    if (props.modelValue) {
        const rawValue = String(props.modelValue).replace(/\D/g, '');
        if (rawValue !== String(props.modelValue)) {
            emit('update:modelValue', rawValue);
        }
    }
}

// Format initial value
ensureFormattedValue();
</script>

<template>
    <div>
        <label :for="id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="mt-1">
            <input
                :id="id"
                type="text"
                :value="formatNumber(modelValue)"
                @input="handleInput"
                :required="required"
                :placeholder="placeholder"
                class="block w-full p-2 text-sm text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': error }"
                inputmode="numeric"
            />
            <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        </div>
    </div>
</template>
