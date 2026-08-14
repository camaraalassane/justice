<template>
    <Card>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gpj-700">{{ title }}</h3>
            <a v-if="exportUrl" :href="exportUrl" class="text-xs text-gpj-500 hover:text-gpj-700 flex items-center gap-1 shrink-0">
                <i class="pi pi-download text-xs"></i> Excel
            </a>
        </div>
        <div v-if="data.length" class="space-y-2">
            <div v-for="(item, i) in data" :key="i" class="flex items-center gap-2 text-xs">
                <span :class="['w-3 h-3 rounded-full shrink-0', colors[i % colors.length]]"></span>
                <span class="flex-1">{{ item.genre || item.libelle }}</span>
                <span class="font-bold text-gpj-600">{{ item.nombre }}</span>
                <span class="text-gpj-400 w-12 text-right">{{ item.pourcentage }}%</span>
            </div>
        </div>
        <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucune donnée</p>
    </Card>
</template>

<script setup>
import Card from '@/Components/GPJ/Card.vue';

defineProps({
    title: String,
    data: Array,
    exportUrl: { type: String, default: null },
});

const colors = ['bg-slate-500', 'bg-amber-500', 'bg-red-500', 'bg-sky-500', 'bg-purple-500', 'bg-emerald-500'];
</script> 