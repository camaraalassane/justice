<template>
    <div>
        <label v-if="label" :for="inputId" class="block text-sm font-medium text-slate-700 mb-1.5">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <i :class="prefixIcon"></i>
            </span>
            <input
                :id="inputId"
                :value="modelValue"
                :type="type"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="inputClasses"
                @input="$emit('update:modelValue', $event.target.value)"
                v-bind="$attrs"
            />
        </div>
        <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
        <p v-if="hint && !error" class="mt-1 text-sm text-slate-500">{{ hint }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    label: String,
    type: {
        type: String,
        default: 'text',
    },
    placeholder: String,
    error: String,
    hint: String,
    disabled: Boolean,
    required: Boolean,
    prefixIcon: String,
    inputId: {
        type: String,
        default: () => `input-${Math.random().toString(36).slice(2)}`,
    },
});

defineEmits(['update:modelValue']);

const inputClasses = computed(() => {
    return [
        'w-full rounded-lg border bg-white text-slate-900 placeholder-slate-400 transition-all duration-200',
        'focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500',
        'shadow-sm',
        props.disabled ? 'bg-slate-50 text-slate-400 cursor-not-allowed' : '',
        props.error ? 'border-red-400 focus:ring-red-500 focus:border-red-500' : 'border-slate-300 hover:border-slate-400',
        props.prefixIcon ? 'pl-10 pr-3 py-2.5' : 'px-3 py-2.5',
    ].join(' ');
});
</script>