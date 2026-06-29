<template>
    <div :class="cardClasses">
        <div v-if="title || $slots.header" class="px-6 py-4 border-b border-gpj-200">
            <h3 v-if="title" class="text-lg font-semibold text-gpj-800">{{ title }}</h3>
            <slot name="header" />
        </div>
        <div :class="padding ? 'p-6' : ''">
            <slot />
        </div>
        <div v-if="$slots.footer" class="px-6 py-3 border-t border-gpj-200 bg-gpj-50 rounded-b-xl">
            <slot name="footer" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: String,
    padding: {
        type: Boolean,
        default: true,
    },
    hover: {
        type: Boolean,
        default: false,
    },
});

const cardClasses = computed(() => {
    return [
        'bg-white rounded-xl border border-gpj-200 shadow-sm',
        props.hover ? 'hover:shadow-md transition-shadow duration-200' : '',
    ].join(' ');
});
</script>