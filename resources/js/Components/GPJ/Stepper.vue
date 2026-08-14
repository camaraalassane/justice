<template>
    <div class="flex items-center w-full">
        <template v-for="(step, index) in steps" :key="index">
            <!-- Étape -->
            <div class="flex items-center" :class="{ 'flex-1': index < steps.length - 1 }">
                <div class="flex flex-col items-center">
                    <div
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300',
                            stepClasses(index)
                        ]"
                    >
                        <i v-if="index < currentStep" class="pi pi-check"></i>
                        <span v-else>{{ index + 1 }}</span>
                    </div>
                    <span
                        :class="[
                            'mt-1 text-xs font-medium whitespace-nowrap',
                            index <= currentStep ? 'text-slate-800' : 'text-slate-400'
                        ]"
                    >
                        {{ step }}
                    </span>
                </div>
                <!-- Ligne de connexion -->
                <div
                    v-if="index < steps.length - 1"
                    :class="[
                        'flex-1 h-1 mx-2 rounded transition-all duration-300',
                        index < currentStep ? 'bg-slate-500' : 'bg-slate-200'
                    ]"
                ></div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    steps: { type: Array, required: true },
    currentStep: { type: Number, default: 0 },
});

const stepClasses = (index) => {
    if (index < props.currentStep) return 'bg-gpj-500 text-white';
    if (index === props.currentStep) return 'bg-gpj-500 text-white ring-4 ring-gpj-200';
    return 'bg-white border-2 border-slate-300 text-slate-500';
};
</script>