<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { formatNumber } from '@/helpers/currencyHelper';

interface TransactionData {
    date: string;
    value: number;
}

interface Props {
    period: 'daily' | 'weekly' | 'monthly' | 'yearly';
    data?: TransactionData[];
}

const props = withDefaults(defineProps<Props>(), {
    data: () => []
});

// Use data from server or create sample data if none provided
const chartData = computed(() => {
    if (props.data && props.data.length > 0) return props.data;

    // Sample data based on period
    const now = new Date();
    const result: TransactionData[] = [];

    if (props.period === 'daily') {
        // Last 24 hours
        for (let i = 0; i < 24; i++) {
            const hour = 23 - i;
            result.push({
                date: `${hour}:00`,
                value: Math.floor(Math.random() * 50) + 10
            });
        }
    } else if (props.period === 'weekly') {
        // Last 7 days
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            result.push({
                date: days[d.getDay()],
                value: Math.floor(Math.random() * 200) + 50
            });
        }
    } else if (props.period === 'monthly') {
        // Last 30 days
        for (let i = 29; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            result.push({
                date: `${d.getDate()}/${d.getMonth() + 1}`,
                value: Math.floor(Math.random() * 500) + 100
            });
        }
    } else {
        // Last 12 months
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for (let i = 11; i >= 0; i--) {
            const d = new Date();
            d.setMonth(d.getMonth() - i);
            result.push({
                date: months[d.getMonth()],
                value: Math.floor(Math.random() * 3000) + 500
            });
        }
    }

    return result;
});

// Extract series data and categories
const series = computed(() => [{
    name: 'Transactions',
    data: chartData.value.map(item => item.value)
}]);

const categories = computed(() => chartData.value.map(item => item.date));

// Chart title based on period
const chartTitle = computed(() => {
    switch (props.period) {
        case 'daily': return 'Transactions (Last 24 Hours)';
        case 'weekly': return 'Transactions (Last 7 Days)';
        case 'monthly': return 'Transactions (Last 30 Days)';
        case 'yearly': return 'Transactions (Last 12 Months)';
        default: return 'Transactions';
    }
});

// Chart options
const chartOptions = ref({
    chart: {
        type: 'area',
        height: 350,
        zoom: {
            enabled: false
        },
        toolbar: {
            show: false
        },
        fontFamily: 'inherit'
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth',
        width: 5
    },
    colors: ['#EC417A'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.3,
            stops: [0, 90, 100]
        }
    },
    grid: {
        borderColor: '#3D223C',
        row: {
            colors: ['transparent', 'transparent'],
            opacity: 0.1
        }
    },
    markers: {
        size: 5
    },
    xaxis: {
        categories: categories.value,
        title: {
            text: 'Time Period',
            style: {
                color: 'white'
            }
        },
        labels: {
            style: {
                colors: 'white'
            }
        },
        axisBorder: {
            show: true,
            color: 'white'
        },
        axisTicks: {
            show: true,
            color: 'white'
        }
    },
    yaxis: {
        title: {
            text: 'Sales (IDR)',
            style: {
                color: 'white'
            }
        },
        labels: {
            formatter: function (val: any) {
                return formatNumber(val);
            },
            style: {
                colors: 'white'
            }
        },
        axisBorder: {
            show: true,
            color: 'white'
        },
        axisTicks: {
            show: true,
            color: 'white'
        },
        min: 0
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (val: any) {
                return formatNumber(val);
            },
            show: true
        },
        x: {
            formatter: function (val: any) {
                return formatNumber(val);
            },
            show: true
        }
    },
    title: {
        text: chartTitle.value,
        align: 'left',
        style: {
            fontSize: '16px',
            fontWeight: 'bold',
            color: 'white'
        }
    }
});

// Update chart when period changes
watch(() => props.period, () => {
    updateChart();
});

// Update chart when data changes
watch(() => props.data, () => {
    updateChart();
}, { deep: true });

const updateChart = () => {
    chartOptions.value = {
        ...chartOptions.value,
        title: {
            ...chartOptions.value.title,
            text: chartTitle.value
        },
        xaxis: {
            ...chartOptions.value.xaxis,
            categories: categories.value
        }
    };
};

onMounted(() => {
    updateChart();
});
</script>

<template>
    <div class="w-full h-full">
        <VueApexCharts height="350" :options="chartOptions" :series="series"></VueApexCharts>
    </div>
</template>
