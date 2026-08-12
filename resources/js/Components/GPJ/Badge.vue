<template>
    <span :class="badgeClasses">
        <slot />
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
    },
    size: {
        type: String,
        default: 'md',
    },
});

const badgeClasses = computed(() => {
    const base = 'inline-flex items-center font-medium rounded-full';

    const variants = {
        default: 'bg-gpj-100 text-gpj-700',
        primary: 'bg-purple-100 text-purple-700',
        success: 'bg-emerald-100 text-emerald-700',
        warning: 'bg-amber-100 text-amber-700',
        danger: 'bg-red-100 text-red-700',
        info: 'bg-sky-100 text-sky-700',
        neutral: 'bg-gray-100 text-gray-700',
    };

    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-0.5 text-sm',
        lg: 'px-3 py-1 text-sm',
    };

    return [base, variants[props.variant], sizes[props.size]].join(' ');
});
</script>