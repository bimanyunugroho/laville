<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import TransactionChart from '@/components/dashboard/TransactionChart.vue';
import SalesDonutChart from '@/components/dashboard/SalesDonutChart.vue';

// Props dari controller
interface Props {
    title: string;
    desc: string;
    data: {
        stats: Array<{
            value: number;
            label: string;
            growth: number;
            type: 'number' | 'rupiah' | 'percent';
        }>;
        transactions: Array<{
            date: string;
            value: number;
        }>;
        salesBySource: Array<{
            source: string;
            value: number;
        }>;
        period: 'daily' | 'weekly' | 'monthly' | 'yearly';
        periods: Array<string>;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
];

// Filter
const dateFilters = ['daily', 'weekly', 'monthly', 'yearly'] as const;
type TimeFilter = typeof dateFilters[number];
const timeFilter = ref<TimeFilter>(props.data.period || 'daily');

// Animation state
const isAnimating = ref(true);
const animateCharts = ref(false);
const currentStats = computed(() =>
    props.data.stats.map(stat => ({
        ...stat,
        value: Number(stat.value),
        growth: Number(stat.growth)
    }))
);
const transactionData = computed(() =>
    (props.data.transactions || []).map(t => ({
        ...t,
        value: Number(t.value)
    }))
);
const salesBySourceData = computed(() =>
    (props.data.salesBySource || []).map(s => ({
        ...s,
        value: Number(s.value)
    }))
);

// Progress for animations (0-100)
const animationProgress = ref(0);
const animationDuration = 1500; // ms

// Watch for changes in timeFilter
watch(timeFilter, (newFilter) => {
    router.get(route('admin.dashboard'), { period: newFilter }, {
        preserveState: true,
        preserveScroll: true,
        only: ['data']
    });

    isAnimating.value = true;
    animateCharts.value = false;
    animationProgress.value = 0;

    // Restart animations
    setTimeout(() => {
        isAnimating.value = false;
        startChartAnimation();
    }, 300);
});

// Animation function - incremental counter
function startChartAnimation() {
    animateCharts.value = true;
    animationProgress.value = 0;

    const startTime = Date.now();

    function updateProgress() {
        const currentTime = Date.now();
        const elapsed = currentTime - startTime;

        if (elapsed < animationDuration) {
            // Calculate progress percentage (0-100)
            animationProgress.value = Math.floor((elapsed / animationDuration) * 100);
            requestAnimationFrame(updateProgress);
        } else {
            animationProgress.value = 100;
        }
    }

    requestAnimationFrame(updateProgress);
}

onMounted(() => {
    setTimeout(() => {
        isAnimating.value = false;
        startChartAnimation();
    }, 800);
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">

            <!-- Header -->
            <div class="flex items-center justify-between animate-fade-in mb-4">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Dashboard Overview</h1>
                <div class="flex space-x-2 bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                    <button v-for="filter in dateFilters" :key="filter" @click="timeFilter = filter" :class="[
                        'px-3 py-1 text-sm font-medium rounded-md transition-all duration-200',
                        timeFilter === filter
                            ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-sm'
                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                    ]">
                        {{ filter.charAt(0).toUpperCase() + filter.slice(1) }}
                    </button>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-slide-up">
                <StatCard v-for="(stat, i) in currentStats" :key="i" :value="stat.value" :label="stat.label"
                    :growth="stat.growth" :type="stat.type" :animate="isAnimating" />
            </div>

            <!-- Charts Section -->
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="w-full sm:w-2/3 bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
                    <TransactionChart
                        :period="timeFilter"
                        :data="transactionData"
                        :animate="animateCharts"
                        :animationProgress="animationProgress" />
                </div>
                <div class="w-full sm:w-1/3 bg-gray-100 dark:bg-gray-800 rounded-lg p-4">
                    <SalesDonutChart
                        :data="salesBySourceData"
                        :animate="animateCharts"
                        :animationProgress="animationProgress" />
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fadeIn 0.5s ease-out forwards;
}

.animate-slide-up {
    animation: slideUp 0.6s ease-out forwards;
}
</style>
