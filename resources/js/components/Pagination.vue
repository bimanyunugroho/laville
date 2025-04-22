<script setup lang="ts">
import { useUnitStore } from '@/stores/unitStore';

// Definisikan tipe untuk setiap link pagination
interface PaginationLink {
    label: string | number;
    url: string | null;
    active: boolean;
}

// Terima array links dari Laravel pagination
const props = defineProps<{
    links: PaginationLink[];
    currentPage: number;
    perPage: number;
    total: number;
}>();

const unitStore = useUnitStore();

const goToPage = (url: string | null) => {
    if (url) {
        unitStore.goToPage(url);
    }
};

// Mendapatkan URL untuk prev dan next dari array links
const getPrevLink = (): string | null => {
    const prevLink = props.links.find(link => link.label === 'Previous');
    return prevLink ? prevLink.url : null;
};

const getNextLink = (): string | null => {
    const nextLink = props.links.find(link => link.label === 'Next');
    return nextLink ? nextLink.url : null;
};
</script>
<template>
    <div v-if="total > 0" class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-4">
        <div>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan
                <span class="font-semibold">{{ ((currentPage - 1) * perPage) + 1 }}</span>
                sampai
                <span class="font-semibold">{{ Math.min(currentPage * perPage, total) }}</span>
                dari
                <span class="font-semibold">{{ total }}</span>
                hasil
            </p>
        </div>

        <nav class="inline-flex shadow-sm rounded-md overflow-hidden" aria-label="Pagination">
            <button type="button" @click="goToPage(getPrevLink())" :disabled="!getPrevLink()"
                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                :class="{ 'opacity-50 cursor-not-allowed': !getPrevLink() }">
                ←
            </button>

            <template v-for="(link, i) in links.slice(1, -1)" :key="i">
                <button v-if="typeof link.label === 'number'" type="button" @click="goToPage(link.url)"
                    :disabled="!link.url" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600" :class="{
                        'bg-indigo-600 text-white': link.active,
                        'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active
                    }">
                    {{ link.label }}
                </button>
            </template>

            <button type="button" @click="goToPage(getNextLink())" :disabled="!getNextLink()"
                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700"
                :class="{ 'opacity-50 cursor-not-allowed': !getNextLink() }">
                →
            </button>
        </nav>
    </div>
</template>
