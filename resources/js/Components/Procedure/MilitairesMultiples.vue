<template>
    <div class="space-y-4">
        <!-- En-tête avec le toggle plurialité -->
        <div class="flex items-center justify-between bg-gpj-50 dark:bg-gpj-800 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="estPlurielleLocal"
                        class="sr-only peer"
                        @change="onTogglePlurialite"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-gpj-500 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-gpj-500"></div>
                    <span class="ms-3 text-sm font-medium text-gpj-700 dark:text-gpj-300">
                        {{ estPlurielleLocal ? 'Pluriel' : 'Individuel' }}
                    </span>
                </label>
                <span class="text-xs text-gpj-400">
                    {{ estPlurielleLocal ? 'Plusieurs militaires concernés' : 'Un seul militaire concerné' }}
                </span>
            </div>
            <span class="text-xs text-gpj-500 bg-gpj-100 dark:bg-gpj-700 px-2 py-1 rounded-full">
                {{ militairesLocal.length }} militaire{{ militairesLocal.length > 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Liste des militaires -->
        <div v-if="militairesLocal.length === 0" class="text-center py-8 text-gpj-400 border-2 border-dashed border-gpj-200 rounded-lg">
            <i class="pi pi-user-plus text-3xl block mb-2"></i>
            <p class="text-sm">Aucun militaire ajouté</p>
            <p class="text-xs">Ajoutez un militaire pour commencer</p>
        </div>

        <!-- Cartes des militaires -->
        <div v-for="(militaire, index) in militairesLocal" :key="index" class="border border-gpj-200 dark:border-gpj-700 rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gpj-500 bg-gpj-100 dark:bg-gpj-700 px-2 py-1 rounded-full">
                        #{{ index + 1 }}
                    </span>
                    <span v-if="militaire.militaire_id" class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                        <i class="pi pi-check-circle text-xs mr-1"></i> Existant
                    </span>
                    <span v-else-if="militaire.nom && militaire.prenom" class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                        <i class="pi pi-plus-circle text-xs mr-1"></i> Nouveau
                    </span>
                </div>
                <button
                    v-if="militairesLocal.length > 1 && estPlurielleLocal"
                    type="button"
                    @click="supprimerMilitaire(index)"
                    class="text-red-400 hover:text-red-600 transition-colors"
                    title="Supprimer ce militaire"
                >
                    <i class="pi pi-trash"></i>
                </button>
            </div>

            <!-- Recherche/sélection du militaire -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="relative">
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Rechercher un militaire existant</label>
                    <SearchSelect
                        :options="optionsMilitaires"
                        v-model="militaire.militaire_id"
                        placeholder="Rechercher par nom, matricule..."
                        @search="(query) => rechercherMilitaires(query, index)"
                        @change="onMilitaireChange(index)"
                    />
                </div>

                <div class="border-t md:border-t-0 md:border-l border-gpj-200 dark:border-gpj-700 md:pl-3 pt-3 md:pt-0">
                    <p class="text-xs font-medium text-gpj-500 mb-2">OU créer un nouveau militaire</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input
                                v-model="militaire.nom"
                                type="text"
                                placeholder="Nom *"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div>
                            <input
                                v-model="militaire.prenom"
                                type="text"
                                placeholder="Prénom *"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div>
                            <input
                                v-model="militaire.matricule"
                                type="text"
                                placeholder="Matricule"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div>
                            <input
                                v-model="militaire.grade"
                                type="text"
                                placeholder="Grade"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Infractions - toujours visibles pour chaque militaire -->
            <div class="mt-4 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-list mr-1"></i> Infractions
                    </label>
                    <span class="text-xs text-gpj-400">{{ (militaire.infractions || []).length }} sélectionnée(s)</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="inf in infractions"
                        :key="inf.id"
                        class="flex items-center gap-1.5 text-xs cursor-pointer px-2 py-1 rounded border border-gpj-200 hover:bg-gpj-50 transition-colors"
                        :class="{
                            'bg-gpj-50 border-gpj-400': (militaire.infractions || []).includes(inf.id)
                        }"
                    >
                        <input
                            type="checkbox"
                            :value="inf.id"
                            v-model="militaire.infractions"
                            class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500"
                        />
                        <span>{{ inf.libelle }}</span>
                        <span class="text-gpj-400 text-[10px]">{{ inf.code_infraction }}</span>
                    </label>
                </div>
            </div>

            <!-- Fautes militaires - toujours visibles -->
            <div class="mt-3 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-exclamation-triangle mr-1"></i> Fautes militaires
                    </label>
                    <button
                        type="button"
                        @click="ajouterFaute(index)"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter
                    </button>
                </div>
                <div v-if="(militaire.fautes_militaires || []).length === 0" class="text-xs text-gpj-400 py-1">
                    Aucune faute
                </div>
                <div v-for="(faute, fi) in militaire.fautes_militaires" :key="fi" class="flex items-center gap-2 mb-1">
                    <input
                        v-model="faute.libelle"
                        placeholder="Libellé"
                        class="flex-1 rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    />
                    <input
                        v-model="faute.description"
                        placeholder="Description (optionnel)"
                        class="flex-1 rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    />
                    <button
                        type="button"
                        @click="supprimerFaute(index, fi)"
                        class="text-red-400 hover:text-red-600 text-xs"
                    >
                        <i class="pi pi-times"></i>
                    </button>
                </div>
            </div>

            <!-- Parties civiles - toujours visibles -->
            <div class="mt-3 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-users mr-1"></i> Parties civiles
                    </label>
                    <button
                        type="button"
                        @click="ajouterPartieCivile(index)"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter
                    </button>
                </div>
                <div v-if="(militaire.parties_civiles || []).length === 0" class="text-xs text-gpj-400 py-1">
                    Aucune partie civile
                </div>
                <div v-for="(pc, pi) in militaire.parties_civiles" :key="pi" class="grid grid-cols-3 gap-2 mb-2 p-2 bg-gpj-50 dark:bg-gpj-800 rounded">
                    <select v-model="pc.type" class="rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="Personne">Personne</option>
                        <option value="Structure">Structure</option>
                    </select>
                    <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <div class="flex items-center gap-2">
                        <input v-if="pc.type === 'Personne'" v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        <button
                            type="button"
                            @click="supprimerPartieCivile(index, pi)"
                            class="text-red-400 hover:text-red-600 text-xs shrink-0"
                        >
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton ajouter - visible uniquement en mode pluriel -->
        <button
            v-if="estPlurielleLocal"
            type="button"
            @click="ajouterMilitaire"
            class="w-full py-3 border-2 border-dashed border-gpj-300 rounded-lg text-sm text-gpj-500 hover:border-gpj-500 hover:text-gpj-600 hover:bg-gpj-50 transition-colors"
        >
            <i class="pi pi-plus mr-2"></i>
            Ajouter un autre militaire
        </button>

        <!-- Champs phases (hérités du parent) -->
        <slot name="phase-fields"></slot>
    </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import SearchSelect from '@/Components/GPJ/SearchSelect.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    estPlurielle: {
        type: Boolean,
        default: false
    },
    infractions: {
        type: Array,
        default: () => []
    },
    militairesOptions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'update:estPlurielle', 'change']);

const militairesLocal = ref(props.modelValue || []);
const estPlurielleLocal = ref(props.estPlurielle);
const optionsMilitaires = ref(props.militairesOptions || []);

// Initialiser avec un militaire si vide
if (militairesLocal.value.length === 0) {
    militairesLocal.value.push({
        militaire_id: null,
        nom: '',
        prenom: '',
        grade: '',
        matricule: '',
        infractions: [],
        fautes_militaires: [],
        parties_civiles: [],
        est_nouveau: true
    });
}

// En mode individuel, on ne garde qu'un seul militaire
watch(estPlurielleLocal, (newVal) => {
    if (!newVal && militairesLocal.value.length > 1) {
        // Garder seulement le premier militaire
        const first = militairesLocal.value[0];
        militairesLocal.value = [first];
        emitChange();
    }
    emit('update:estPlurielle', newVal);
});

// Watchers pour synchroniser avec le parent
watch(() => props.modelValue, (newVal) => {
    if (JSON.stringify(newVal) !== JSON.stringify(militairesLocal.value)) {
        militairesLocal.value = newVal || [];
    }
}, { deep: true });

watch(() => props.estPlurielle, (newVal) => {
    estPlurielleLocal.value = newVal;
});

// Watcher pour émettre les changements
watch(militairesLocal, () => {
    emitChange();
}, { deep: true });

const emitChange = () => {
    emit('update:modelValue', militairesLocal.value);
    emit('change', militairesLocal.value);
};

const ajouterMilitaire = () => {
    if (!estPlurielleLocal.value) return;
    militairesLocal.value.push({
        militaire_id: null,
        nom: '',
        prenom: '',
        grade: '',
        matricule: '',
        infractions: [],
        fautes_militaires: [],
        parties_civiles: [],
        est_nouveau: true
    });
    emitChange();
};

const supprimerMilitaire = (index) => {
    if (militairesLocal.value.length > 1 && estPlurielleLocal.value) {
        militairesLocal.value.splice(index, 1);
        emitChange();
    }
};

const rechercherMilitaires = async (query, index) => {
    if (!query || query.length < 2) {
        optionsMilitaires.value = props.militairesOptions || [];
        return;
    }
    try {
        const response = await fetch(`/api/militaires/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        optionsMilitaires.value = data;
    } catch (e) {
        console.error('Erreur recherche:', e);
    }
};

const onMilitaireChange = (index) => {
    const mil = militairesLocal.value[index];
    if (mil.militaire_id) {
        const selected = optionsMilitaires.value.find(m => m.value === mil.militaire_id);
        if (selected) {
            mil.nom = selected.label.split(' ')[0] || '';
            mil.prenom = selected.label.split(' ').slice(1).join(' ') || '';
            mil.matricule = selected.sublabel || '';
        }
        mil.est_nouveau = false;
    } else {
        mil.est_nouveau = true;
    }
    emitChange();
};

const onTogglePlurialite = () => {
    estPlurielleLocal.value = !estPlurielleLocal.value;
};

const ajouterFaute = (index) => {
    if (!militairesLocal.value[index].fautes_militaires) {
        militairesLocal.value[index].fautes_militaires = [];
    }
    militairesLocal.value[index].fautes_militaires.push({ libelle: '', description: '' });
    emitChange();
};

const supprimerFaute = (index, fauteIndex) => {
    militairesLocal.value[index].fautes_militaires.splice(fauteIndex, 1);
    emitChange();
};

const ajouterPartieCivile = (index) => {
    if (!militairesLocal.value[index].parties_civiles) {
        militairesLocal.value[index].parties_civiles = [];
    }
    militairesLocal.value[index].parties_civiles.push({ 
        type: 'Personne', 
        nom: '', 
        prenom: '', 
        profession: '', 
        adresse: '' 
    });
    emitChange();
};

const supprimerPartieCivile = (index, pcIndex) => {
    militairesLocal.value[index].parties_civiles.splice(pcIndex, 1);
    emitChange();
};

// Exposer
defineExpose({
    militaires: militairesLocal,
    estPlurielle: estPlurielleLocal,
    validate: () => {
        for (const mil of militairesLocal.value) {
            if (!mil.militaire_id && (!mil.nom || !mil.prenom)) {
                return false;
            }
        }
        return true;
    }
});
</script>