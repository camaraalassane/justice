<template>
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide">
                <i class="pi pi-users mr-2"></i> Parties civiles <span class="text-red-500">*</span>
            </h3>
            <div class="flex gap-2">
                <button type="button" @click="ajouter('Personne')" class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-user-plus"></i> Personne
                </button>
                <button type="button" @click="ajouter('Structure')" class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-building"></i> Structure
                </button>
            </div>
        </div>

        <div v-for="(pc, index) in modelValue" :key="index" class="p-4 bg-slate-50 rounded-lg border border-slate-200 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <i :class="pc.type === 'Structure' ? 'pi pi-building' : 'pi pi-user'" class="text-gpj-500 text-sm"></i>
                    <span class="text-xs font-medium text-gpj-500">
                        {{ pc.type === 'Structure' ? 'Structure' : 'Personne' }} {{ index + 1 }}
                    </span>
                </div>
                <button v-if="modelValue.length > 1" type="button" @click="supprimer(index)" class="text-red-400 hover:text-red-600 text-xs cursor-pointer">
                    <i class="pi pi-times-circle"></i> Supprimer
                </button>
            </div>

            <!-- Structure : seul le nom -->
            <div v-if="pc.type === 'Structure'">
                <label class="block text-xs font-medium text-gpj-600 mb-1">Nom de la structure <span class="text-red-500">*</span></label>
                <input v-model="pc.nom" type="text" required placeholder="Ex: État Malien, Armée de Terre, Société X..." class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
            </div>

            <!-- Personne -->
            <div v-if="pc.type === 'Personne'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input v-model="pc.nom" type="text" required placeholder="Nom" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input v-model="pc.prenom" type="text" required placeholder="Prénom" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Profession</label>
                    <input v-model="pc.profession" type="text" placeholder="Profession" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Adresse</label>
                    <input v-model="pc.adresse" type="text" placeholder="Adresse" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue']);

const ajouter = (type) => {
    emit('update:modelValue', [...props.modelValue, {
        type,
        nom: '',
        prenom: '',
        profession: '',
        adresse: '',
    }]);
};

const supprimer = (index) => {
    emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
};
</script>