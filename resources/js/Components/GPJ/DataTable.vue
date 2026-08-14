<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 shadow-sm">
        <!-- En-tête avec recherche -->
        <div v-if="searchable || $slots.toolbar" class="px-4 py-3 bg-white border-b border-slate-200 flex flex-wrap items-center gap-3">
            <div v-if="searchable" class="relative flex-1 max-w-sm">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher..."
                    class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                    @input="onSearch"
                />
            </div>
            <slot name="toolbar" />
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-700 border-b-2 border-slate-200">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="['px-4 py-3 text-left font-semibold tracking-wide text-xs uppercase', col.sortable ? 'cursor-pointer hover:bg-slate-100 select-none' : '']"
                            @click="col.sortable && sort(col.key)"
                        >
                            <div class="flex items-center gap-1">
                                {{ col.label }}
                                <span v-if="sortColumn === col.key" class="text-slate-600">
                                    <i :class="sortDirection === 'asc' ? 'pi pi-sort-up-fill' : 'pi pi-sort-down-fill'"></i>
                                </span>
                                <span v-else-if="col.sortable" class="text-slate-300">
                                    <i class="pi pi-sort"></i>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr
                        v-for="(row, index) in paginatedData"
                        :key="row.id || index"
                        class="bg-white hover:bg-slate-50 transition-colors"
                    >
                        <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-slate-800">
                            <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                                {{ row[col.key] }}
                            </slot>
                        </td>
                    </tr>
                    <tr v-if="!paginatedData.length">
                        <td :colspan="columns.length" class="px-4 py-12 text-center text-slate-400">
                            <i class="pi pi-inbox text-3xl mb-2 block"></i>
                            {{ emptyMessage }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="px-4 py-3 bg-white border-t border-slate-200 flex items-center justify-between">
            <span class="text-sm text-slate-500">
                {{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, data.length) }} sur {{ data.length }} résultats
            </span>
            <div class="flex items-center gap-1">
                <button
                    :disabled="currentPage === 1"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed"
                    @click="currentPage = 1"
                >
                    <i class="pi pi-angle-double-left text-xs"></i>
                </button>
                <button
                    :disabled="currentPage === 1"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed"
                    @click="currentPage--"
                >
                    <i class="pi pi-angle-left text-xs"></i>
                </button>
                <span class="px-3 py-1 text-sm font-medium bg-gpj-500 text-white rounded-lg">{{ currentPage }}</span>
                <button
                    :disabled="currentPage === totalPages"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed"
                    @click="currentPage++"
                >
                    <i class="pi pi-angle-right text-xs"></i>
                </button>
                <button
                    :disabled="currentPage === totalPages"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed"
                    @click="currentPage = totalPages"
                >
                    <i class="pi pi-angle-double-right text-xs"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, default: () => [] },
    perPage: { type: Number, default: 10 },
    searchable: { type: Boolean, default: true },
    emptyMessage: { type: String, default: 'Aucune donnée trouvée' },
});

const emit = defineEmits(['search', 'sort']);

const searchQuery = ref('');
const currentPage = ref(1);
const sortColumn = ref(null);
const sortDirection = ref('asc');
let debounceTimer = null;

const filteredData = computed(() => props.data);

const sortedData = computed(() => {
    if (!sortColumn.value) return filteredData.value;
    return [...filteredData.value].sort((a, b) => {
        const valA = a[sortColumn.value];
        const valB = b[sortColumn.value];
        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });
});

const totalPages = computed(() => Math.ceil(sortedData.value.length / props.perPage) || 1);

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * props.perPage;
    return sortedData.value.slice(start, start + props.perPage);
});

const onSearch = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        currentPage.value = 1;
        emit('search', searchQuery.value);
    }, 300);
};

const sort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    emit('sort', { column, direction: sortDirection.value });
};

watch(() => props.data, () => {
    currentPage.value = 1;
});
</script>