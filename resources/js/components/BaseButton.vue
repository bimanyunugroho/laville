<script setup lang="ts">
const props = defineProps<{
    loading?: boolean
    type?: 'submit' | 'button' | 'reset'
    text?: string
    loadingText?: string
    color?: 'blue' | 'red' | 'green' | 'orange' | 'emerald'
}>()

const colorClass = {
    blue: {
        base: 'bg-blue-600 dark:bg-blue-500',
        hover: 'hover:bg-blue-700 dark:hover:bg-blue-600',
        ring: 'focus:ring-blue-500 dark:focus:ring-blue-400'
    },
    red: {
        base: 'bg-red-600 dark:bg-red-500',
        hover: 'hover:bg-red-700 dark:hover:bg-red-600',
        ring: 'focus:ring-red-500 dark:focus:ring-red-400'
    },
    green: {
        base: 'bg-green-600 dark:bg-green-500',
        hover: 'hover:bg-green-700 dark:hover:bg-green-600',
        ring: 'focus:ring-green-500 dark:focus:ring-green-400'
    },
    orange: {
        base: 'bg-orange-600 dark:bg-orange-500',
        hover: 'hover:bg-orange-700 dark:hover:bg-orange-600',
        ring: 'focus:ring-orange-500 dark:focus:ring-orange-400'
    },
    emerald: {
        base: 'bg-emerald-600 dark:bg-emerald-500',
        hover: 'hover:bg-emerald-700 dark:hover:bg-emerald-600',
        ring: 'focus:ring-emerald-500 dark:focus:ring-emerald-400'
    }
}

const currentColor = colorClass[props.color || 'blue']
</script>

<template>
    <button :type="type || 'submit'" :disabled="loading" class="inline-flex justify-center items-center rounded-md border border-transparent
           px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-150
           focus:outline-none focus:ring-2 focus:ring-offset-2
           disabled:opacity-50 disabled:cursor-not-allowed
           cursor-pointer" :class="[currentColor.base, currentColor.hover, currentColor.ring]">
        <span>{{ loading ? (loadingText || 'Menyimpan...') : (text || 'Simpan') }}</span>
    </button>
</template>
