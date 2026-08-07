<template>
    <Link
        :href="href"
        :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
            active
                ? 'bg-gpj-500 text-white shadow-md'
                : 'text-gpj-200 hover:bg-gpj-700 hover:text-white'
        ]"
    >
        <i :class="icon" class="text-base w-5 text-center shrink-0"></i>
        <span class="truncate"><slot /></span>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'; 

defineProps({
    href: String,
    active: Boolean,
    icon: String,
});
</script>