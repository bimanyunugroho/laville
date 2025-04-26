<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

interface Option {
    label: string;
    value: any;
    disabled?: boolean;
}

interface Props {
    modelValue: any;
    options: any[];
    placeholder?: string;
    multiple?: boolean;
    searchable?: boolean;
    clearable?: boolean;
    loading?: boolean;
    label?: string;
    valueProperty?: string;
    labelProperty?: string;
    disabled?: boolean;
    required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select option',
    multiple: false,
    searchable: true,
    clearable: true,
    loading: false,
    label: '',
    valueProperty: 'id',
    labelProperty: 'name',
    disabled: false,
    required: false,
});

const emit = defineEmits(['update:modelValue', 'change']);
const selected = ref<any>(null);

const formattedOptions = computed<Option[]>(() =>
    props.options.map((item) => ({
        label: item[props.labelProperty],
        value: item[props.valueProperty],
        disabled: item.disabled ?? false,
    }))
);

const handleChange = (val: any) => {
    selected.value = val;
    const emittedValue = props.multiple
        ? val?.map((v: any) => v.value ?? v)
        : val?.value ?? val;

    emit('update:modelValue', emittedValue);
    emit('change', emittedValue);
};

watch(
    () => props.modelValue,
    (newVal) => {
        if (props.multiple && Array.isArray(newVal)) {
            selected.value = newVal
                .map((val: any) => formattedOptions.value.find((opt) => opt.value === val))
                .filter(Boolean);
        } else {
            selected.value =
                formattedOptions.value.find((opt) => opt.value === newVal) ?? null;
        }
    },
    { immediate: true }
);
</script>

<template>
    <div class="select2-container">
        <label v-if="label" class="block text-sm font-medium text-gray-200">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <v-select :model-value="selected" @update:modelValue="handleChange" :options="formattedOptions"
            :multiple="multiple" :searchable="searchable" :clearable="clearable" :placeholder="placeholder"
            :disabled="disabled" :loading="loading" class="vue-select-container text-sm py-1">
            <template v-if="$slots['no-options']" #no-options>
                <slot name="no-options"></slot>
            </template>
        </v-select>
    </div>
</template>

<style>
.vue-select-container {
    width: 100%;
}

.vs__dropdown-toggle {
    padding: 0.3rem 0.7rem;
    border-radius: 0.3rem;
    border: 1px solid #374151;
    background-color: #1f2937;
    color: #f9fafb;
}

.vs__dropdown-menu {
    border-radius: 0.3rem;
    margin-top: 0.25rem;
    background-color: #1f2937;
    color: #f9fafb;
    z-index: 1000;
    border: 1px solid #374151;
}

.vs__selected {
    background-color: #374151;
    color: #f9fafb;
    border-radius: 0.25rem;
    margin: 0px;
}

.vs__search {
    background-color: #1f2937;
    color: #f9fafb;
}

.vs__search::placeholder {
    color: #9ca3af;
}

.vs__clear {
    fill: #9ca3af;
}

.vs__open-indicator {
    fill: #d1d5db;
}

.vs--disabled .vs__dropdown-toggle {
    background-color: #4b5563;
    opacity: 0.5;
}

.vs__spinner {
    align-self: center;
    color: #f3f4f6;
    font-size: 0.875rem;
}
</style>
