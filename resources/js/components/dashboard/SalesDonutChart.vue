<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { formatNumber } from '@/helpers/currencyHelper';

interface SalesData {
    source: string;
    value: number;
}

interface Props {
    data?: SalesData[];
}

const props = withDefaults(defineProps<Props>(), {
    data: () => []
});

// Create sample data if none provided
const sampleData = computed(() => {
    if (props.data && props.data.length > 0) return props.data;

    // Default sample data
    return [
        { source: 'OFFLINE', value: 44 },
        { source: 'SHOPEE', value: 55 },
        { source: 'TOKOPEDIA', value: 41 },
        { source: 'INSTAGRAM', value: 17 }
    ];
});

// Format series data for ApexCharts
const series = computed(() => sampleData.value.map(item => item.value));
const labels = computed(() => sampleData.value.map(item => item.source));

// Define colors for different channels
const channelColors: Record<string, string> = {
    'SHOPEE': '#FF6900',
    'INSTAGRAM': '#F6339A',
    'TOKOPEDIA': '#00C951',
    'OFFLINE': '#AD46FF'
};

// Chart options
const chartOptions = ref({
    chart: {
        type: 'donut',
        fontFamily: 'inherit'
    },
    labels: labels.value,
    colors: labels.value.map(label => channelColors[label] || '#cbd5e1'),
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        fontSize: '14px',
        labels: {
            colors: 'white' // Set legend label color to white
        },
        markers: {
            width: 12,
            height: 12,
            radius: 12
        }
    },
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 400,
                        color: 'white' // Label (category name)
                    },
                    value: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 400,
                        color: 'white' // Value (e.g., amount)
                    },
                    total: {
                        show: true,
                        showAlways: true,
                        label: 'Total Sales',
                        fontSize: '16px',
                        fontWeight: 600,
                        color: 'white', // Total label color
                        formatter: function (w: any) {
                            const sum = w.globals.seriesTotals.reduce((a: any, b: any) => a + b, 0);
                            return formatNumber(sum);
                        }
                    }
                }
            },
            borderWidth: 0 // Remove border around the donut
        }
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                width: 300
            },
            legend: {
                position: 'bottom'
            }
        }
    }],
    dataLabels: {
        enabled: true,
        formatter: function (val: any, opts: any) {
            return formatNumber(opts.w.globals.seriesTotals[opts.seriesIndex]);
        },
        style: {
            colors: ['white']
        }
    },
    tooltip: {
        theme: 'dark',
        style: {
            fontSize: '14px',
            color: 'white'
        },
        y: {
            formatter: function (val: any) {
                return formatNumber(val);
            }
        }
    },
    title: {
        text: 'Sales by Channel',
        align: 'left',
        style: {
            fontSize: '16px',
            fontWeight: 'bold',
            color: 'white'
        }
    }
});


// Update chart when data changes
watch(() => props.data, () => {
    updateChart();
}, { deep: true });

const updateChart = () => {
    chartOptions.value = {
        ...chartOptions.value,
        labels: labels.value
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
