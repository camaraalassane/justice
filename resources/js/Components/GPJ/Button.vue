<template>
    <button
        :class="buttonClasses"
        :disabled="disabled || loading"
        v-bind="$attrs"
    >
        <i v-if="loading" class="pi pi-spin pi-spinner mr-2"></i>
        <i v-if="icon && !loading" :class="icon" class="mr-2"></i>
        <slot />
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'outline', 'danger', 'ghost'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    icon: String,
    loading: Boolean,
    disabled: Boolean,
    block: Boolean,
});

const buttonClasses = computed(() => {
    const base = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

    const variants = {
        primary: 'bg-gpj-500 text-white hover:bg-gpj-600 focus:ring-slate-500 shadow-sm',
        secondary: 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400 shadow-sm',
        outline: 'border-2 border-slate-500 text-slate-600 hover:bg-slate-50 focus:ring-slate-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        ghost: 'text-slate-600 hover:bg-slate-100 focus:ring-slate-400',
    };

    const sizes = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
    };

    return [
        base,
        variants[props.variant],
        sizes[props.size],
        props.block ? 'w-full' : '',
    ].join(' ');
});
</script>