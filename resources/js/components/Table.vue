<script setup lang="ts">
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-200 text-gray-800 dark:text-gray-200">
                <slot name="header"></slot>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                <slot name="body"></slot>
            </tbody>
        </table>
    </div>
</template>
