<template>
    <div :class="['rounded-xl border p-5 transition-all duration-200', 'border-slate-200 bg-white hover:shadow-md']">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ label }}</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ value }}</p>
            </div>
            <div :class="['w-12 h-12 rounded-lg flex items-center justify-center', iconBg]">
                <i :class="icon" class="text-xl" :style="{ color: iconColor }"></i>
            </div>
        </div>
        <div v-if="trend !== undefined" class="mt-3 flex items-center gap-1">
            <i
                :class="trend >= 0 ? 'pi pi-arrow-up text-emerald-500' : 'pi pi-arrow-down text-red-500'"
                class="text-xs"
            ></i>
            <span :class="trend >= 0 ? 'text-emerald-600' : 'text-red-600'" class="text-sm font-medium">
                {{ Math.abs(trend) }}%
            </span>
            <span class="text-sm text-slate-400">vs mois dernier</span>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    label: String,
    value: [String, Number],
    icon: String,
    iconBg: { type: String, default: 'bg-gpj-100' },
    iconColor: { type: String, default: '#2d5a3d' },
    trend: Number,
});
</script>