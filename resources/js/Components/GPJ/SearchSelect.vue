<template>
    <div class="relative" ref="wrapperRef">
        <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1.5">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <div class="relative">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input
                :value="searchQuery"
                @input="onSearch"
                @focus="showDropdown = true"
                @keydown.escape="showDropdown = false"
                @keydown.enter.prevent="selectHighlighted"
                @keydown.arrow-down.prevent="highlightNext"
                @keydown.arrow-up.prevent="highlightPrev"
                type="text"
                :placeholder="placeholder"
                class="w-full pl-10 pr-8 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 transition-all shadow-sm"
                autocomplete="off"
            />
            <button
                v-if="modelValue"
                type="button"
                @click="clearSelection"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
                <i class="pi pi-times text-xs"></i>
            </button>
            <i v-if="loading" class="pi pi-spin pi-spinner absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        </div>

        <div v-if="selectedItem" class="mt-2 flex items-center gap-2 p-2 bg-slate-50 rounded-lg border border-slate-200">
            <div class="w-8 h-8 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-700 text-xs font-bold shrink-0">
                {{ selectedItemLabel.charAt(0) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-800">{{ selectedItemLabel }}</p>
                <p class="text-xs text-slate-500">{{ selectedItemSublabel }}</p>
            </div>
            <button type="button" @click="clearSelection" class="text-slate-400 hover:text-red-500 shrink-0">
                <i class="pi pi-times-circle text-sm"></i>
            </button>
        </div>

        <div
            v-if="showDropdown && filteredOptions.length > 0"
            class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
            <div
                v-for="(option, index) in filteredOptions"
                :key="option.value"
                @click="selectOption(option)"
                @mouseenter="highlightedIndex = index"
                :class="[
                    'px-4 py-2.5 cursor-pointer transition-colors text-sm flex items-center gap-3',
                    highlightedIndex === index ? 'bg-gpj-50 text-slate-900' : 'text-slate-700 hover:bg-slate-50'
                ]"
            >
                <div class="w-8 h-8 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-700 text-xs font-bold shrink-0">
                    {{ option.label.charAt(0) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">{{ option.label }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ option.sublabel }}</p>
                </div>
            </div>
        </div>

        <div
            v-if="showDropdown && searchQuery && filteredOptions.length === 0 && !loading"
            class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg p-4 text-center text-sm text-slate-400"
        >
            <i class="pi pi-inbox text-xl mb-1 block"></i>
            Aucun résultat trouvé
        </div>

        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    placeholder: {
        type: String,
        default: 'Rechercher...',
    },
    required: Boolean,
    error: String,
    options: {
        type: Array,
        default: () => [],
    },
    loading: Boolean,
    searchUrl: String,
});

const emit = defineEmits(['update:modelValue', 'search']);

const wrapperRef = ref(null);
const searchQuery = ref('');
const showDropdown = ref(false);
const highlightedIndex = ref(-1);

const selectedItem = computed(() => {
    return props.options.find(o => o.value == props.modelValue) || null;
});

const selectedItemLabel = computed(() => {
    return selectedItem.value?.label || '';
});

const selectedItemSublabel = computed(() => {
    return selectedItem.value?.sublabel || '';
});

const filteredOptions = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.options.slice(0, 50);
    }
    const query = searchQuery.value.toLowerCase().trim();
    return props.options.filter(option => {
        const label = (option.label || '').toLowerCase();
        const sublabel = (option.sublabel || '').toLowerCase();
        return label.includes(query) || sublabel.includes(query);
    }).slice(0, 50);
});

let debounceTimer = null;
const onSearch = (e) => {
    searchQuery.value = e.target.value;
    showDropdown.value = true;
    highlightedIndex.value = -1;

    if (props.searchUrl) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            emit('search', searchQuery.value);
        }, 300);
    }
};

const selectOption = (option) => {
    emit('update:modelValue', option.value);
    searchQuery.value = '';
    showDropdown.value = false;
    highlightedIndex.value = -1;
};

const clearSelection = () => {
    emit('update:modelValue', null);
    searchQuery.value = '';
    showDropdown.value = false;
};

const highlightNext = () => {
    if (highlightedIndex.value < filteredOptions.value.length - 1) {
        highlightedIndex.value++;
    }
};

const highlightPrev = () => {
    if (highlightedIndex.value > 0) {
        highlightedIndex.value--;
    }
};

const selectHighlighted = () => {
    if (highlightedIndex.value >= 0 && filteredOptions.value[highlightedIndex.value]) {
        selectOption(filteredOptions.value[highlightedIndex.value]);
    }
};

// Gestion du clic extérieur
const handleClickOutside = (event) => {
    if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    if (props.modelValue) {
        searchQuery.value = '';
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch(() => props.modelValue, () => {
    searchQuery.value = '';
    showDropdown.value = false;
});
</script>