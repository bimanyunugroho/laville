<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Info } from 'lucide-vue-next';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage();
const noPeriodExists = computed(() => page.props.noPeriodExists);
const closedPeriodMessage = computed(() => page.props.closedPeriodMessage);
</script>

<template>
    <header
        class="flex h-10 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-10 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Badge yang menampilkan pesan jika noPeriodExists true -->
        <template v-if="noPeriodExists === true">
            <span class="ml-auto flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-700 via-violet-600 to-indigo-600 rounded-full shadow-xl transform transition-all hover:scale-105 hover:shadow-2xl">
                <Info class="w-4 h-4 mr-3" /> <!-- Icon Lucide -->
                <span class="whitespace-nowrap">{{ closedPeriodMessage }}</span>
            </span>
        </template>

        <template v-else-if="noPeriodExists === false && closedPeriodMessage !== null">
            <span class="ml-auto flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-700 via-rose-600 to-red-600 rounded-full shadow-xl transform transition-all hover:scale-105 hover:shadow-2xl">
                <Info class="w-4 h-4 mr-3" /> <!-- Icon Lucide -->
                <span class="whitespace-nowrap">{{ closedPeriodMessage }}</span>
            </span>
        </template>

    </header>
</template>

