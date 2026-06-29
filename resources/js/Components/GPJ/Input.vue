<template>
    <div>
        <label v-if="label" :for="inputId" class="block text-sm font-medium text-gpj-700 mb-1">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        <div class="relative">
            <span v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gpj-400">
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
        <p v-if="hint && !error" class="mt-1 text-sm text-gpj-400">{{ hint }}</p>
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
        'w-full rounded-lg border bg-white text-gpj-900 placeholder-gpj-300 transition-colors duration-200',
        'focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500',
        props.disabled ? 'bg-gpj-50 text-gpj-400 cursor-not-allowed' : '',
        props.error ? 'border-red-500 focus:ring-red-500' : 'border-gpj-200 hover:border-gpj-400',
        props.prefixIcon ? 'pl-10 pr-3 py-2' : 'px-3 py-2',
    ].join(' ');
});
</script>