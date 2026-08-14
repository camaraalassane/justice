<template>
    <div class="space-y-4">
        <!-- ========================================================== -->
        <!-- CONDAMNATION - ORDRE DE POURSUITE                          -->
        <!-- ========================================================== -->
        <div v-if="isOrdrePoursuite" class="p-4 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800">
            <div class="flex items-center gap-4 flex-wrap">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        :checked="localCondamnation.est_condamne === true"
                        @change="onCondamnationChange($event)"
                        class="w-4 h-4 rounded border-slate-400 text-slate-600 focus:ring-slate-500"
                    />
                    <span class="text-sm font-medium text-slate-800 dark:text-slate-400">
                        <i class="pi pi-gavel mr-1"></i>
                        Condamné
                    </span>
                </label>

                <!-- Champ Peine - apparaît seulement si condamné -->
                <div v-if="localCondamnation.est_condamne === true" class="flex-1 min-w-50">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-slate-800 dark:text-slate-400 whitespace-nowrap">
                            Peine :
                        </label>
                        <input 
                            :value="localCondamnation.peine_principale"
                            @input="updatePeine($event)"
                            type="text"
                            placeholder="Ex: 5 ans d'emprisonnement"
                            class="flex-1 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        />
                    </div>
                    <div class="mt-2">
                        <textarea 
                            :value="localCondamnation.peine_description"
                            @input="updatePeineDescription($event)"
                            rows="2"
                            placeholder="Description détaillée de la peine..."
                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Affichage de la peine enregistrée -->
            <div v-if="localCondamnation.est_condamne === true && localCondamnation.peine_principale" class="mt-2 text-xs text-emerald-600 dark:text-emerald-400">
                <i class="pi pi-check-circle mr-1"></i>
                Peine : <strong>{{ localCondamnation.peine_principale }}</strong>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- CHAMPS EXISTANTS                                           -->
        <!-- ========================================================== -->
        <div v-if="champs && champs.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Champs ({{ champs.length }})</h4>
                <button type="button" @click="ajouterChamp" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter un champ
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div v-for="(champ, i) in champs" :key="'champ-'+i">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-medium text-slate-700">{{ formatLabel(champ.cle) }}</label>
                        <button type="button" @click="supprimerChamp(i)" class="text-red-400 hover:text-red-600 text-xs"><i class="pi pi-times"></i></button>
                    </div>
                    <input v-if="champ.type === 'text'" v-model="champ.valeur" type="text" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    <input v-else-if="champ.type === 'date'" v-model="champ.valeur" type="date" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    <textarea v-else-if="champ.type === 'textarea'" v-model="champ.valeur" rows="2" class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- PERSONNES                                                  -->
        <!-- ========================================================== -->
        <div v-if="personnes && personnes.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Personnes</h4>
                <button type="button" @click="ajouterPersonne" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter
                </button>
            </div>
            <div v-for="(p, i) in personnes" :key="'pers-'+i" class="p-3 bg-white rounded-lg border border-slate-200 mb-2">
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-slate-600">Personne {{ i + 1 }}</span>
                    <button type="button" @click="personnes.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input v-model="p.nom" placeholder="Nom *" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    <input v-model="p.prenom" placeholder="Prénom *" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    <input v-model="p.profession" placeholder="Profession" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    <input v-model="p.autre" placeholder="Autre" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- ÉVÉNEMENTS                                                 -->
        <!-- ========================================================== -->
        <div v-if="evenements && evenements.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Événements</h4>
                <button type="button" @click="ajouterEvenement" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter
                </button>
            </div>
            <div v-for="(e, i) in evenements" :key="'ev-'+i" class="p-3 bg-white rounded-lg border border-slate-200 mb-2">
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-slate-600">Événement {{ i + 1 }}</span>
                    <button type="button" @click="evenements.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </div>
                <input v-model="e.nom" placeholder="Nom *" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <input v-model="e.date_evenement" type="date" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <textarea v-model="e.description" placeholder="Description" rows="2" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- RÉFÉRENCES                                                 -->
        <!-- ========================================================== -->
        <div v-if="references && references.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Références</h4>
                <button type="button" @click="ajouterReference" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter
                </button>
            </div>
            <div v-for="(r, i) in references" :key="'ref-'+i" class="p-3 bg-white rounded-lg border border-slate-200 mb-2">
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-slate-600">Référence {{ i + 1 }}</span>
                    <button type="button" @click="references.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </div>
                <input v-model="r.libelle" placeholder="Libellé *" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <textarea v-model="r.description" placeholder="Description" rows="2" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- OPTIONS À COCHER                                           -->
        <!-- ========================================================== -->
        <div v-if="optionsCocher && optionsCocher.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Options</h4>
                <button type="button" @click="ajouterOptionCocher" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter une option
                </button>
            </div>
            <div class="space-y-2">
                <label v-for="(o, i) in optionsCocher" :key="'opt-'+i" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" v-model="o.est_coche" class="rounded border-slate-400 text-slate-600 focus:ring-slate-500" />
                    <span class="text-sm text-slate-800 flex-1">{{ o.libelle }}</span>
                    <button type="button" @click="optionsCocher.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </label>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- PIÈCES JOINTES                                             -->
        <!-- ========================================================== -->
        <div v-if="piecesJointes && piecesJointes.length" class="border-t border-slate-300 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-xs font-semibold text-slate-600 uppercase">Pièces jointes</h4>
                <button type="button" @click="ajouterPieceJointe" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1">
                    <i class="pi pi-plus-circle"></i> Ajouter
                </button>
            </div>
            <div v-for="(pj, i) in piecesJointes" :key="'pj-'+i" class="p-3 bg-white rounded-lg border border-slate-200 mb-2">
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-slate-600">Pièce {{ i + 1 }}</span>
                    <button type="button" @click="piecesJointes.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </div>
                <input v-model="pj.nom" placeholder="Nom *" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <textarea v-model="pj.description" placeholder="Description" rows="2" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                <div class="mt-2">
                    <label class="block text-xs text-slate-600 mb-1">Fichier PDF</label>
                    <input type="file" accept=".pdf" @change="(e) => onFileChange(e, i)" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-slate-100 file:text-slate-700" />
                    <div v-if="pj.fichier" class="text-xs text-emerald-600 mt-1"><i class="pi pi-check mr-1"></i>{{ pj.fichier.name }}</div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- AJOUT DE CHAMPS PERSONNALISÉS                              -->
        <!-- ========================================================== -->
        <div class="border-t border-slate-300 pt-4 space-y-2">
            <button type="button" @click="showCustomForm = !showCustomForm" class="text-xs text-slate-600 hover:text-slate-800 font-medium flex items-center gap-1 cursor-pointer">
                <i class="pi pi-plus-circle"></i> Ajouter un champ personnalisé
            </button>
            <div v-if="showCustomForm" class="p-4 bg-white rounded-lg border border-slate-200 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Nom <span class="text-red-500">*</span></label>
                        <input v-model="newField.cle" type="text" placeholder="Nom du champ" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-600 mb-1">Type</label>
                        <select v-model="newField.type" class="w-full rounded border border-slate-300 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="text">Texte</option>
                            <option value="date">Date</option>
                            <option value="textarea">Texte long</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="ajouterNouveauChamp" class="px-3 py-1.5 bg-gpj-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 cursor-pointer">Ajouter</button>
                    <button type="button" @click="showCustomForm = false; resetNewField()" class="px-3 py-1.5 border border-slate-300 text-slate-700 text-xs rounded-lg hover:bg-slate-50 cursor-pointer">Annuler</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    phaseTypeId: { type: [String, Number], default: null },
    phaseTypes: { type: Array, default: () => [] },
    champs: { type: Array, default: () => [] },
    personnes: { type: Array, default: () => [] },
    evenements: { type: Array, default: () => [] },
    references: { type: Array, default: () => [] },
    optionsCocher: { type: Array, default: () => [] },
    piecesJointes: { type: Array, default: () => [] },
    modelValue: { type: Object, default: () => ({}) },
});

const emit = defineEmits([
    'update:champs', 'update:personnes', 'update:evenements', 
    'update:references', 'update:optionsCocher', 'update:piecesJointes',
    'update:modelValue', 'update:estCondamne', 'update:peinePrincipale', 'update:peineDescription'
]);

// ================================================================
// CONDAMNATION - ÉTAT LOCAL
// ================================================================

const localCondamnation = ref({
    est_condamne: false,
    peine_principale: '',
    peine_description: '',
});

// Initialiser avec les valeurs du modèle
onMounted(() => {
    if (props.modelValue) {
        localCondamnation.value.est_condamne = props.modelValue.est_condamne === true;
        localCondamnation.value.peine_principale = props.modelValue.peine_principale || '';
        localCondamnation.value.peine_description = props.modelValue.peine_description || '';
    }
});

// Watcher pour synchroniser les changements du modèle parent
watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        localCondamnation.value.est_condamne = newVal.est_condamne === true;
        localCondamnation.value.peine_principale = newVal.peine_principale || '';
        localCondamnation.value.peine_description = newVal.peine_description || '';
    }
}, { deep: true });

// ================================================================
// CONDAMNATION - MÉTHODES
// ================================================================

// Vérifier si c'est un ordre de poursuite
const isOrdrePoursuite = computed(() => {
    if (!props.phaseTypeId || props.phaseTypeId === 'autre') return false;
    const phaseType = props.phaseTypes.find(pt => pt.id == props.phaseTypeId);
    return phaseType?.slug === 'ordre_de_poursuite';
});

// Émettre les changements de condamnation
const emitCondamnationUpdate = () => {
    const data = {
        est_condamne: localCondamnation.value.est_condamne === true,
        peine_principale: localCondamnation.value.est_condamne ? localCondamnation.value.peine_principale : null,
        peine_description: localCondamnation.value.est_condamne ? localCondamnation.value.peine_description : null,
    };
    
    emit('update:modelValue', { ...props.modelValue, ...data });
    emit('update:estCondamne', data.est_condamne);
    emit('update:peinePrincipale', data.peine_principale);
    emit('update:peineDescription', data.peine_description);
};

// Changement du checkbox
const onCondamnationChange = (event) => {
    const checked = event.target.checked;
    localCondamnation.value.est_condamne = checked;
    
    if (!checked) {
        localCondamnation.value.peine_principale = '';
        localCondamnation.value.peine_description = '';
        // Supprimer le champ peine des champs
        const index = props.champs.findIndex(c => c.cle === 'peine');
        if (index !== -1) {
            props.champs.splice(index, 1);
        }
        const descIndex = props.champs.findIndex(c => c.cle === 'peine_description');
        if (descIndex !== -1) {
            props.champs.splice(descIndex, 1);
        }
    } else {
        // Ajouter le champ peine si la valeur existe
        if (localCondamnation.value.peine_principale) {
            addPeineToChamps(localCondamnation.value.peine_principale);
        }
        if (localCondamnation.value.peine_description) {
            addPeineDescriptionToChamps(localCondamnation.value.peine_description);
        }
    }
    
    emitCondamnationUpdate();
};

// Mise à jour de la peine
const updatePeine = (event) => {
    const value = event.target.value;
    localCondamnation.value.peine_principale = value;
    
    if (localCondamnation.value.est_condamne) {
        addPeineToChamps(value);
    }
    emitCondamnationUpdate();
};

// Mise à jour de la description de la peine
const updatePeineDescription = (event) => {
    const value = event.target.value;
    localCondamnation.value.peine_description = value;
    
    if (localCondamnation.value.est_condamne) {
        addPeineDescriptionToChamps(value);
    }
    emitCondamnationUpdate();
};

// Ajouter la peine aux champs
const addPeineToChamps = (value) => {
    if (!value) return;
    
    const existingIndex = props.champs.findIndex(c => c.cle === 'peine');
    if (existingIndex !== -1) {
        props.champs[existingIndex].valeur = value;
    } else {
        props.champs.push({
            cle: 'peine',
            valeur: value,
            type: 'text'
        });
    }
    emit('update:champs', props.champs);
};

// Ajouter la description de la peine aux champs
const addPeineDescriptionToChamps = (value) => {
    if (!value) return;
    
    const existingIndex = props.champs.findIndex(c => c.cle === 'peine_description');
    if (existingIndex !== -1) {
        props.champs[existingIndex].valeur = value;
    } else {
        props.champs.push({
            cle: 'peine_description',
            valeur: value,
            type: 'textarea'
        });
    }
    emit('update:champs', props.champs);
};

// Watcher pour les changements de phaseTypeId
watch(() => props.phaseTypeId, (newVal) => {
    if (newVal && newVal !== 'autre') {
        const phaseType = props.phaseTypes.find(pt => pt.id == newVal);
        if (phaseType?.slug !== 'ordre_de_poursuite') {
            localCondamnation.value.est_condamne = false;
            localCondamnation.value.peine_principale = '';
            localCondamnation.value.peine_description = '';
            emitCondamnationUpdate();
        }
    }
}, { immediate: true });

// ================================================================
// AUTRES MÉTHODES (champs, personnes, événements, etc.)
// ================================================================

const showCustomForm = ref(false);
const newField = ref({ cle: '', type: 'text' });

const formatLabel = (c) => (c || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

const resetNewField = () => { newField.value = { cle: '', type: 'text' }; };

const ajouterChamp = () => {
    props.champs.push({ cle: 'nouveau_champ', valeur: '', type: 'text' });
    emit('update:champs', props.champs);
};

const supprimerChamp = (i) => {
    props.champs.splice(i, 1);
    emit('update:champs', props.champs);
};

const ajouterNouveauChamp = () => {
    if (!newField.value.cle.trim()) {
        alert('Nom du champ requis.');
        return;
    }
    props.champs.push({
        cle: newField.value.cle.trim().toLowerCase().replace(/\s+/g, '_'),
        valeur: '',
        type: newField.value.type,
        _custom: true
    });
    emit('update:champs', props.champs);
    showCustomForm.value = false;
    resetNewField();
};

const ajouterOptionCocher = () => {
    const libelle = prompt('Libellé de l\'option :');
    if (libelle?.trim()) {
        props.optionsCocher.push({ libelle: libelle.trim(), est_coche: false, _custom: true });
        emit('update:optionsCocher', props.optionsCocher);
    }
};

const ajouterPersonne = () => {
    props.personnes.push({ nom: '', prenom: '', profession: '', autre: '' });
    emit('update:personnes', props.personnes);
};

const ajouterEvenement = () => {
    props.evenements.push({ nom: '', date_evenement: '', description: '' });
    emit('update:evenements', props.evenements);
};

const ajouterReference = () => {
    props.references.push({ libelle: '', description: '' });
    emit('update:references', props.references);
};

const ajouterPieceJointe = () => {
    props.piecesJointes.push({ nom: '', description: '', contexte: '' });
    emit('update:piecesJointes', props.piecesJointes);
};

const onFileChange = (e, i) => {
    const fichier = e.target.files[0];
    if (fichier && props.piecesJointes?.[i]) {
        props.piecesJointes[i].fichier = fichier;
        emit('update:piecesJointes', props.piecesJointes);
    }
};
</script>