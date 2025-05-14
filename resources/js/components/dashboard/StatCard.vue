<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { TrendingUpIcon, TrendingDownIcon, Trophy, ShoppingCart, BarChart2, Package2 } from 'lucide-vue-next'
import { formatNumber } from '@/helpers/currencyHelper'

interface Props {
    value: number
    label: string
    growth: number
    type: 'number' | 'rupiah' | 'percent'
    animate?: boolean
}

const props = defineProps<Props>()

// Pemilihan icon sesuai label
const statIcon = computed(() => {
    if (props.label.includes('Profit')) {
        return Trophy
    } else if (props.label.includes('Conversion')) {
        return BarChart2
    } else if (props.label.includes('Purchase')) {
        return Package2
    } else if (props.label.includes('Order')) {
        return ShoppingCart
    } else {
        return Trophy
    }
})

// Warna background sesuai kategori label
const bgColor = computed(() => {
    if (props.label.includes('Profit')) {
        return 'bg-green-100 dark:bg-green-900/30'
    } else if (props.label.includes('Conversion')) {
        return 'bg-orange-100 dark:bg-orange-900/30'
    } else if (props.label.includes('Order')) {
        return 'bg-pink-100 dark:bg-pink-900/30'
    } else if (props.label.includes('Purchase')) {
        return 'bg-purple-100 dark:bg-purple-900/30'
    } else {
        return 'bg-gray-100 dark:bg-gray-700/30'
    }
})

// Warna teks sesuai kategori label
const textColor = computed(() => {
    if (props.label.includes('Profit')) {
        return 'text-green-500'
    } else if (props.label.includes('Conversion')) {
        return 'text-orange-500'
    } else if (props.label.includes('Orders')) {
        return 'text-pink-500'
    } else if (props.label.includes('Purchase')) {
        return 'text-purple-500'
    } else {
        return 'text-gray-500'
    }
})

// Warna icon sama dengan teks
const iconColor = textColor

// Counter animation
const displayValue = ref(0)
const animationDuration = 1500

watch(() => props.value, (newVal) => {
    animateCounter(newVal)
})

watch(() => props.animate, (shouldAnimate) => {
    if (shouldAnimate) {
        animateCounter(props.value)
    }
})

onMounted(() => {
    animateCounter(props.value)
})

function animateCounter(target: number) {
    const startValue = 0
    const startTime = performance.now()

    function updateCounter(currentTime: number) {
        const elapsed = currentTime - startTime
        if (elapsed < animationDuration) {
            const progress = elapsed / animationDuration
            const easedProgress = -progress * (progress - 2)
            displayValue.value = Math.floor(startValue + (target - startValue) * easedProgress)
            requestAnimationFrame(updateCounter)
        } else {
            displayValue.value = target
        }
    }

    requestAnimationFrame(updateCounter)
}

const growthClass = computed(() => {
    return props.growth >= 0 ? 'text-green-500' : 'text-red-500'
})

const growthSign = computed(() => {
    return props.growth >= 0 ? '+' : ''
})
</script>

<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 relative">
        <!-- Header: Growth dan Icon -->
        <div class="flex justify-between items-start">

            <!-- Kiri: Vertikal, Growth, Value, Label -->
            <div class="flex flex-col items-start justify-center space-y-2 text-center">
                <!-- Growth -->
                <div :class="['flex items-center', growthClass, 'text-sm font-medium']">
                    {{ growthSign }}{{ growth.toFixed(1) }}%
                    <component :is="growth >= 0 ? TrendingUpIcon : TrendingDownIcon" class="ml-1 w-4 h-4" />
                </div>
                <!-- Value utama -->
                <div class="text-3xl font-bold text-gray-800 dark:text-white">
                    <template v-if="props.type === 'rupiah'">{{ formatNumber(displayValue) }}</template>
                    <template v-else-if="props.type === 'percent'">{{ displayValue }}%</template>
                    <template v-else>{{ formatNumber(displayValue) }}</template>
                </div>
                <!-- Label -->
                <p class="text-gray-500 dark:text-gray-400 text-sm">{{ label }}</p>
            </div>

            <!-- Kanan: Icon besar tanpa animasi -->
            <div class="flex items-center justify-center">
                <div :class="[
                    bgColor,
                    'p-4 rounded-lg shadow-lg transition-all duration-300 ease-in-out transform hover:scale-105 hover:-rotate-2 hover:shadow-xl hover:translate-y-1'
                ]">
                    <component :is="statIcon" :class="[
                        'w-12 h-12',
                        iconColor,
                        'transition-transform duration-300 ease-in-out hover:scale-110 hover:-rotate-3 hover:shadow-lg'
                    ]" />
                </div>
            </div>
        </div>
    </div>
</template>
