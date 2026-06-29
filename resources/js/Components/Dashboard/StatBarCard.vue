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
                <span class="w-5 h-5 rounded bg-gpj-100 text-gpj-600 flex items-center justify-center text-xs font-bold shrink-0">{{ i + 1 }}</span>
                <span class="flex-1 truncate">{{ item[labelKey] || item.armee || item.libelle }}</span>
                <div class="w-24 bg-gray-100 rounded-full h-2">
                    <div :class="color" class="h-2 rounded-full" :style="{ width: (item.nombre / maxVal * 100) + '%' }"></div>
                </div>
                <span class="font-bold text-gpj-600 w-8 text-right">{{ item.nombre }}</span>
            </div>
        </div>
        <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucune donnée</p>
    </Card>
</template>

<script setup>
import { computed } from 'vue';
import Card from '@/Components/GPJ/Card.vue';

const props = defineProps({
    title: String,
    data: Array,
    color: { type: String, default: 'bg-gpj-500' },
    labelKey: { type: String, default: 'armee' },
    exportUrl: { type: String, default: null }, 
});

const maxVal = computed(() => Math.max(...props.data.map(d => d.nombre), 1));
</script>