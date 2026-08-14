<template>
    <div class="space-y-3">
        <!-- Type de parquet - Désactivé si disabled -->
        <div :class="{ 'opacity-50 pointer-events-none': disabled }">
            <label class="block text-sm font-medium text-slate-800 mb-1">
                Type de parquet <span v-if="!disabled" class="text-red-500">*</span>
            </label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="radio" 
                        :checked="parquetType === 'militaire'"
                        @change="setType('militaire')"
                        :disabled="disabled"
                        class="rounded-full border-slate-400 text-slate-600 focus:ring-slate-500"
                    />
                    <span class="text-sm">Militaire</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="radio" 
                        :checked="parquetType === 'droit_commun'"
                        @change="setType('droit_commun')"
                        :disabled="disabled"
                        class="rounded-full border-slate-400 text-slate-600 focus:ring-slate-500"
                    />
                    <span class="text-sm">Droit Commun</span>
                </label>
            </div>
        </div>

        <!-- Sélection du parquet militaire -->
        <div v-if="parquetType === 'militaire'" :class="{ 'opacity-50 pointer-events-none': disabled }">
            <label class="block text-sm font-medium text-slate-800 mb-1">
                Parquet militaire <span v-if="!disabled" class="text-red-500">*</span>
            </label>
            <select 
                v-model="selectedParquetId"
                @change="onMilitaireSelect"
                :disabled="disabled"
                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <option value="">Sélectionner un parquet</option>
                <option 
                    v-for="p in parquetsMilitaires" 
                    :key="p.id" 
                    :value="p.id"
                >
                    {{ p.nom }} {{ p.localisation ? ' - ' + p.localisation : '' }}
                </option>
            </select>
            <p v-if="error || parquetError" class="text-xs text-red-500 mt-1">{{ error || parquetError }}</p>
        </div>

        <!-- Sélection ou création de parquet de droit commun -->
        <div v-if="parquetType === 'droit_commun'" :class="{ 'opacity-50 pointer-events-none': disabled }">
            <label class="block text-sm font-medium text-slate-800 mb-1">
                Parquet de droit commun <span v-if="!disabled" class="text-red-500">*</span>
            </label>
            
            <select 
                v-model="selectedDroitCommunId"
                @change="onDroitCommunSelect"
                :disabled="disabled"
                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <option value="">Sélectionner un parquet</option>
                <option 
                    v-for="p in parquetsDroitCommun" 
                    :key="p.id" 
                    :value="p.id"
                >
                    {{ p.nom }} {{ p.localisation ? ' - ' + p.localisation : '' }}
                </option>
                <option v-if="!disabled" value="__nouveau__" class="text-slate-600 font-medium">--- Créer un nouveau ---</option>
            </select>

            <div v-if="showNouveauParquet && !disabled" class="mt-3 p-3 bg-slate-50 rounded-lg border border-slate-300">
                <p class="text-xs font-medium text-slate-700 mb-2">📝 Nouveau parquet de droit commun</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            Nom du parquet <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="nouveauParquetNom"
                            type="text"
                            placeholder="Ex: Tribunal de Bamako"
                            class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                            @input="onNouveauParquetChange"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            Localisation
                        </label>
                        <input 
                            v-model="nouveauParquetLocalisation"
                            type="text"
                            placeholder="Ex: Bamako"
                            class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                            @input="onNouveauParquetChange"
                        />
                    </div>
                </div>
                <div class="mt-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Code (optionnel)
                    </label>
                    <input 
                        v-model="nouveauParquetCode"
                        type="text"
                        placeholder="Ex: TRIB-BKO"
                        class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        @input="onNouveauParquetChange"
                    />
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <button 
                        type="button"
                        @click="creerNouveauParquet"
                        :disabled="creerEnCours || !nouveauParquetNom.trim()"
                        class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer"
                    >
                        <i v-if="creerEnCours" class="pi pi-spin pi-spinner mr-1"></i>
                        Ajouter ce parquet
                    </button>
                    <button 
                        type="button"
                        @click="annulerNouveauParquet"
                        class="px-3 py-1.5 border border-slate-300 text-slate-700 text-xs rounded-lg hover:bg-slate-50 cursor-pointer"
                    >
                        Annuler
                    </button>
                </div>
                <p v-if="nouveauParquetError" class="text-xs text-red-500 mt-2">{{ nouveauParquetError }}</p>
            </div>

            <p v-if="error || parquetError" class="text-xs text-red-500 mt-1">{{ error || parquetError }}</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            type: 'militaire',
            id: null,
            nom: '',
            localisation: '',
            code: ''
        })
    },
    parquets: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue', 'change', 'error', 'parquet-created']);

// ====== ÉTATS ======
const parquetType = ref(props.modelValue.type || 'militaire');
const selectedParquetId = ref(props.modelValue.id || null);
const selectedDroitCommunId = ref(null);
const showNouveauParquet = ref(false);
const nouveauParquetNom = ref('');
const nouveauParquetLocalisation = ref('');
const nouveauParquetCode = ref('');
const creerEnCours = ref(false);
const nouveauParquetError = ref(null);
const parquetError = ref(null);

// ====== COMPUTED ======
const parquetsMilitaires = computed(() => {
    return props.parquets.filter(p => p.type === 'militaire');
});

const parquetsDroitCommun = computed(() => {
    return props.parquets.filter(p => p.type === 'droit_commun');
});

// ====== MÉTHODES ======
const setType = (type) => {
    if (props.disabled) return;
    parquetType.value = type;
    if (type === 'militaire') {
        selectedParquetId.value = null;
        selectedDroitCommunId.value = null;
        showNouveauParquet.value = false;
    } else {
        selectedParquetId.value = null;
        if (parquetsDroitCommun.value.length > 0) {
            selectedDroitCommunId.value = null;
        } else {
            showNouveauParquet.value = true;
        }
    }
    emitValue();
};

const onMilitaireSelect = () => {
    if (props.disabled) return;
    emitValue();
};

const onDroitCommunSelect = () => {
    if (props.disabled) return;
    const value = selectedDroitCommunId.value;
    if (value === '__nouveau__') {
        showNouveauParquet.value = true;
        selectedDroitCommunId.value = null;
        nouveauParquetNom.value = '';
        nouveauParquetLocalisation.value = '';
        nouveauParquetCode.value = '';
        nouveauParquetError.value = null;
        emitValue();
    } else if (value) {
        showNouveauParquet.value = false;
        const selected = parquetsDroitCommun.value.find(p => p.id == value);
        if (selected) {
            nouveauParquetNom.value = selected.nom;
            nouveauParquetLocalisation.value = selected.localisation || '';
            nouveauParquetCode.value = selected.code || '';
        }
        emitValue();
    } else {
        emitValue();
    }
};

const onNouveauParquetChange = () => {
    if (props.disabled) return;
    emitValue();
};

const annulerNouveauParquet = () => {
    if (props.disabled) return;
    showNouveauParquet.value = false;
    selectedDroitCommunId.value = null;
    nouveauParquetNom.value = '';
    nouveauParquetLocalisation.value = '';
    nouveauParquetCode.value = '';
    nouveauParquetError.value = null;
    if (parquetsDroitCommun.value.length === 0) {
        showNouveauParquet.value = true;
    }
    emitValue();
};

const creerNouveauParquet = async () => {
    if (props.disabled) return;
    if (!nouveauParquetNom.value.trim()) {
        nouveauParquetError.value = 'Le nom du parquet est requis';
        return;
    }

    creerEnCours.value = true;
    nouveauParquetError.value = null;

    try {
        const response = await fetch('/api/parquets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                nom: nouveauParquetNom.value.trim(),
                localisation: nouveauParquetLocalisation.value,
                code: nouveauParquetCode.value,
            })
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Erreur lors de la création');
        }

        const data = await response.json();
        
        // Ajouter le nouveau parquet à la liste
        if (!props.parquets.find(p => p.id === data.id)) {
            props.parquets.push(data);
        }
        
        selectedDroitCommunId.value = data.id;
        showNouveauParquet.value = false;
        nouveauParquetNom.value = '';
        nouveauParquetLocalisation.value = '';
        nouveauParquetCode.value = '';
        
        emit('parquet-created', data);
        emitValue();

    } catch (error) {
        nouveauParquetError.value = error.message || 'Erreur lors de la création du parquet';
        console.error('Erreur:', error);
    } finally {
        creerEnCours.value = false;
    }
};

const emitValue = () => {
    let value = {};
    let errorMsg = null;
    
    if (parquetType.value === 'militaire') {
        // Militaire - l'ID est obligatoire
        if (selectedParquetId.value) {
            const selected = parquetsMilitaires.value.find(p => p.id == selectedParquetId.value);
            value = {
                type: 'militaire',
                id: parseInt(selectedParquetId.value),
                nom: selected ? selected.nom : '',
                localisation: selected ? selected.localisation : '',
                code: selected ? selected.code : ''
            };
            errorMsg = null;
        } else {
            value = {
                type: 'militaire',
                id: null,
                nom: '',
                localisation: '',
                code: ''
            };
            errorMsg = 'Veuillez sélectionner un parquet militaire';
        }
        parquetError.value = errorMsg;
    } else {
        // Droit commun
        if (selectedDroitCommunId.value) {
            const selected = parquetsDroitCommun.value.find(p => p.id == selectedDroitCommunId.value);
            if (selected) {
                value = {
                    type: 'droit_commun',
                    id: parseInt(selectedDroitCommunId.value),
                    nom: selected.nom,
                    localisation: selected.localisation || '',
                    code: selected.code || ''
                };
                errorMsg = null;
            } else {
                value = {
                    type: 'droit_commun',
                    id: null,
                    nom: '',
                    localisation: '',
                    code: ''
                };
                errorMsg = 'Veuillez sélectionner un parquet de droit commun';
            }
        } else if (showNouveauParquet.value && nouveauParquetNom.value.trim()) {
            value = {
                type: 'droit_commun',
                id: null,
                nom: nouveauParquetNom.value.trim(),
                localisation: nouveauParquetLocalisation.value,
                code: nouveauParquetCode.value
            };
            errorMsg = null;
        } else {
            value = {
                type: 'droit_commun',
                id: null,
                nom: '',
                localisation: '',
                code: ''
            };
            errorMsg = 'Veuillez sélectionner ou créer un parquet de droit commun';
        }
        parquetError.value = errorMsg;
    }
    
    emit('update:modelValue', value);
    emit('change', value);
    emit('error', parquetError.value);
};

// ====== WATCH ======
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        parquetType.value = newVal.type || 'militaire';
        if (newVal.type === 'militaire') {
            selectedParquetId.value = newVal.id || null;
        } else {
            if (newVal.id) {
                selectedDroitCommunId.value = newVal.id;
                showNouveauParquet.value = false;
                nouveauParquetNom.value = newVal.nom || '';
                nouveauParquetLocalisation.value = newVal.localisation || '';
                nouveauParquetCode.value = newVal.code || '';
            } else if (newVal.nom) {
                showNouveauParquet.value = true;
                nouveauParquetNom.value = newVal.nom;
                nouveauParquetLocalisation.value = newVal.localisation || '';
                nouveauParquetCode.value = newVal.code || '';
                selectedDroitCommunId.value = null;
            }
        }
    }
}, { deep: true });

watch(parquetsDroitCommun, (newVal) => {
    if (parquetType.value === 'droit_commun' && newVal.length === 0) {
        showNouveauParquet.value = true;
    }
}, { immediate: true });
</script>