<template>
    <div class="p-4 bg-gpj-50 dark:bg-gpj-800 rounded-lg border border-gpj-200 dark:border-gpj-700">
        <!-- En-tête du militaire -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gpj-100 dark:bg-gpj-700 flex items-center justify-center text-gpj-600 dark:text-gpj-300 font-bold text-sm">
                    {{ getInitiales() }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gpj-800 dark:text-white">{{ getNomComplet() }}</span>
                        <span v-if="estPrincipal" class="text-[10px] bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">Principal</span>
                        <span v-if="procedureMilitaire?.est_nouveau" class="text-[10px] bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full">Nouveau</span>
                    </div>
                    <p class="text-xs text-gpj-400">{{ getMatricule() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a 
                    v-if="procedureMilitaire?.militaire_id"
                    :href="route('militaires.casier', procedureMilitaire.militaire_id)" 
                    target="_blank" 
                    class="text-gpj-400 hover:text-gpj-600 text-xs"
                    title="Imprimer le casier"
                >
                    <i class="pi pi-print"></i>
                </a>
                <button 
                    v-if="peutModifier && !enEdition"
                    @click="activerEdition"
                    class="text-gpj-400 hover:text-gpj-600 text-xs"
                    title="Modifier les informations"
                >
                    <i class="pi pi-pencil"></i>
                </button>
                <button 
                    v-if="peutModifier && enEdition"
                    @click="annulerEdition"
                    class="text-red-400 hover:text-red-600 text-xs"
                >
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>

        <!-- Mode affichage -->
        <div v-if="!enEdition" class="space-y-3">
            <!-- Informations du militaire -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm bg-white dark:bg-gpj-900 p-3 rounded-lg">
                <div><span class="text-gpj-400">Grade:</span> <span class="font-medium">{{ getGrade() }}</span></div>
                <div><span class="text-gpj-400">Unité:</span> <span class="font-medium">{{ getUnite() }}</span></div>
                <div><span class="text-gpj-400">Genre:</span> <span class="font-medium">{{ getGenre() }}</span></div>
                <div><span class="text-gpj-400">Armée:</span> <span class="font-medium">{{ getArmee() }}</span></div>
                <div><span class="text-gpj-400">Statut:</span> <Badge :variant="statutVariant(getStatut())" size="sm">{{ getStatut() }}</Badge></div>
                <div><span class="text-gpj-400">Date naissance:</span> <span class="font-medium">{{ formatDate(getDateNaissance()) }}</span></div>
            </div>

            <!-- Infractions -->
            <div class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-list mr-1"></i> Infractions
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirEditionInfractions"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-pencil text-xs mr-1"></i> Modifier
                    </button>
                </div>
                <div v-if="getInfractionsDisplay().length === 0" class="text-xs text-gpj-400 py-1">Aucune infraction</div>
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="inf in getInfractionsDisplay()" :key="inf.id" variant="neutral" size="sm" class="text-[10px]">
                        {{ inf.libelle }}
                    </Badge>
                </div>
            </div>

            <!-- Fautes -->
            <div class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-exclamation-triangle mr-1"></i> Fautes militaires
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirEditionFautes"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-pencil text-xs mr-1"></i> Modifier
                    </button>
                </div>
                <div v-if="getFautes().length === 0" class="text-xs text-gpj-400 py-1">Aucune faute</div>
                <div v-for="(faute, fi) in getFautes()" :key="fi" class="text-xs text-gpj-600">
                    - {{ faute.libelle }}{{ faute.description ? ' : ' + faute.description : '' }}
                </div>
            </div>

            <!-- Parties civiles -->
            <div class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-users mr-1"></i> Parties civiles
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirEditionPartiesCiviles"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-pencil text-xs mr-1"></i> Modifier
                    </button>
                </div>
                <div v-if="getPartiesCiviles().length === 0" class="text-xs text-gpj-400 py-1">Aucune partie civile</div>
                <div v-for="(pc, pi) in getPartiesCiviles()" :key="pi" class="text-xs text-gpj-600">
                    - {{ pc.type === 'Structure' ? 'Structure: ' + pc.nom : pc.nom + ' ' + (pc.prenom || '') }}{{ pc.profession ? ' (' + pc.profession + ')' : '' }}
                </div>
            </div>
        </div>

        <!-- Mode édition -->
        <div v-if="enEdition" class="space-y-3">
            <!-- Édition des informations du militaire -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-white dark:bg-gpj-900 p-3 rounded-lg">
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Grade</label>
                    <select v-model="editionForm.grade" class="w-full rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="">Sélectionner</option>
                        <option v-for="g in grades" :key="g.id" :value="g.libelle">
                            {{ g.libelle }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Unité</label>
                    <input v-model="editionForm.unite" type="text" placeholder="Unité" class="w-full rounded border border-gpj-200 text-sm py-1 px-2" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Genre</label>
                    <select v-model="editionForm.genre" class="w-full rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="">Sélectionner</option>
                        <option value="Masculin">Masculin</option>
                        <option value="Féminin">Féminin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Armée/Service</label>
                    <select v-model="editionForm.armee" class="w-full rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="">Sélectionner</option>
                        <option v-for="a in armees" :key="a" :value="a">{{ a }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Statut</label>
                    <select v-model="editionForm.statut" class="w-full rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="Actif">Actif</option>
                        <option value="Suspendu">Suspendu</option>
                        <option value="Déserteur">Déserteur</option>
                        <option value="Radié">Radié</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Date de naissance</label>
                    <input v-model="editionForm.date_naissance" type="date" class="w-full rounded border border-gpj-200 text-sm py-1 px-2" />
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center gap-2 pt-2 border-t border-gpj-100">
                <button @click="sauvegarderInfos" :disabled="saving" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                    <i v-if="saving" class="pi pi-spin pi-spinner mr-1"></i>
                    Enregistrer les infos
                </button>
                <button @click="annulerEdition" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                    Annuler
                </button>
            </div>
        </div>

        <!-- Modale édition infractions -->
        <div v-if="showEditInfractions" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Modifier les infractions</h3>
                <div class="space-y-2">
                    <div v-for="inf in allInfractions" :key="inf.id" class="flex items-center gap-2 p-2 hover:bg-gpj-50 rounded">
                        <input 
                            type="checkbox" 
                            :value="inf.id" 
                            v-model="editInfractionsForm" 
                            class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500"
                        />
                        <span class="text-sm">{{ inf.libelle }}</span>
                        <span class="text-xs text-gpj-400">{{ inf.code_infraction }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gpj-100">
                    <button @click="sauvegarderInfractions" :disabled="savingInfractions" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="savingInfractions" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer
                    </button>
                    <button @click="showEditInfractions = false" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale édition fautes -->
        <div v-if="showEditFautes" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Modifier les fautes</h3>
                <div class="space-y-2">
                    <div v-for="(faute, fi) in editFautesForm" :key="fi" class="flex items-center gap-2 p-2 bg-gpj-50 rounded">
                        <input v-model="faute.libelle" placeholder="Libellé" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2" />
                        <input v-model="faute.description" placeholder="Description" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2" />
                        <button @click="editFautesForm.splice(fi, 1)" class="text-red-400 hover:text-red-600 text-xs">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <button @click="editFautesForm.push({ libelle: '', description: '' })" class="text-xs text-gpj-500 hover:text-gpj-700">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter une faute
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gpj-100">
                    <button @click="sauvegarderFautes" :disabled="savingFautes" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="savingFautes" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer
                    </button>
                    <button @click="showEditFautes = false" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale édition parties civiles -->
        <div v-if="showEditPartiesCiviles" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Modifier les parties civiles</h3>
                <div class="space-y-2">
                    <div v-for="(pc, pi) in editPartiesCivilesForm" :key="pi" class="grid grid-cols-4 gap-2 p-2 bg-gpj-50 rounded">
                        <select v-model="pc.type" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="Personne">Personne</option>
                            <option value="Structure">Structure</option>
                        </select>
                        <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        <input v-if="pc.type === 'Personne'" v-model="pc.prenom" placeholder="Prénom" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        <div class="flex items-center gap-2">
                            <input v-model="pc.profession" placeholder="Profession" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <button @click="editPartiesCivilesForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <button @click="editPartiesCivilesForm.push({ type: 'Personne', nom: '', prenom: '', profession: '', adresse: '' })" class="text-xs text-gpj-500 hover:text-gpj-700">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter une partie civile
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gpj-100">
                    <button @click="sauvegarderPartiesCiviles" :disabled="savingPartiesCiviles" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="savingPartiesCiviles" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer
                    </button>
                    <button @click="showEditPartiesCiviles = false" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { Badge } from '@/Components/GPJ';

const props = defineProps({
    procedureMilitaire: {
        type: Object,
        required: true
    },
    procedureId: {
        type: Number,
        required: true
    },
    estPrincipal: {
        type: Boolean,
        default: false
    },
    allInfractions: {
        type: Array,
        default: () => []
    },
    peutModifier: {
        type: Boolean,
        default: false
    },
    grades: {
        type: Array,
        default: () => []
    },
    armees: {
        type: Array,
        default: () => [
            'Armée de Terre',
            'Armée de l\'Air',
            'Garde Nationale',
            'Gendarmerie Nationale',
            'Police Nationale',
            'Protection Civile',
            'Direction du Génie Militaire',
            'Direction du Service de Santé des Armées',
            'Direction du Matériel',
            'Direction des Transmissions',
            'État-Major Général',
            'Autre'
        ]
    }
});

const emit = defineEmits(['updated']);

// ====== ÉTATS ======
const enEdition = ref(false);
const saving = ref(false);
const showEditInfractions = ref(false);
const showEditFautes = ref(false);
const showEditPartiesCiviles = ref(false);
const savingInfractions = ref(false);
const savingFautes = ref(false);
const savingPartiesCiviles = ref(false);

// ====== DONNÉES ======
const editionForm = ref({
    grade: props.procedureMilitaire?.grade_temp || props.procedureMilitaire?.militaire?.grade || '',
    unite: props.procedureMilitaire?.militaire?.unite || '',
    genre: props.procedureMilitaire?.militaire?.genre || '',
    armee: props.procedureMilitaire?.militaire?.armee || '',
    statut: props.procedureMilitaire?.militaire?.statut || 'Actif',
    date_naissance: props.procedureMilitaire?.militaire?.date_naissance ? 
        new Date(props.procedureMilitaire.militaire.date_naissance).toISOString().split('T')[0] : ''
});

const editInfractionsForm = ref([...(props.procedureMilitaire?.infractions || [])]);
const editFautesForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.fautes_militaires || [])));
const editPartiesCivilesForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.parties_civiles || [])));

// ====== MÉTHODES ======
const getInitiales = () => {
    const pm = props.procedureMilitaire;
    if (pm.militaire) {
        return (pm.militaire.nom?.charAt(0) || '') + (pm.militaire.prenoms?.charAt(0) || '');
    }
    return (pm.nom_temp?.charAt(0) || '') + (pm.prenom_temp?.charAt(0) || '');
};

const getNomComplet = () => {
    const pm = props.procedureMilitaire;
    if (pm.militaire) {
        return pm.militaire.nom + ' ' + pm.militaire.prenoms;
    }
    return (pm.nom_temp || 'Nom inconnu') + ' ' + (pm.prenom_temp || '');
};

const getMatricule = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.matricule || pm.matricule_temp || 'Sans matricule';
};

const getGrade = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.grade || pm.grade_temp || '-';
};

const getUnite = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.unite || '-';
};

const getGenre = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.genre || '-';
};

const getArmee = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.armee || '-';
};

const getStatut = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.statut || 'Actif';
};

const getDateNaissance = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.date_naissance || null;
};

const getInfractionsDisplay = () => {
    const ids = props.procedureMilitaire?.infractions || [];
    if (ids.length === 0) return [];
    
    // Chercher dans allInfractions
    const found = props.allInfractions.filter(inf => ids.includes(inf.id));
    
    // Si trouvé, retourner
    if (found.length > 0) {
        return found;
    }
    
    // Sinon, retourner les IDs avec un libellé par défaut
    return ids.map(id => ({
        id: id,
        libelle: 'Infraction #' + id
    }));
};

const getFautes = () => {
    return props.procedureMilitaire?.fautes_militaires || [];
};

const getPartiesCiviles = () => {
    return props.procedureMilitaire?.parties_civiles || [];
};

const formatDate = (d) => {
    if (!d) return '-';
    const date = new Date(d);
    if (isNaN(date.getTime())) return '-';
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const statutVariant = (s) => {
    const m = { Actif: 'success', Suspendu: 'warning', Déserteur: 'danger', Radié: 'neutral' };
    return m[s] || 'default';
};

// ====== ACTIONS ======
const activerEdition = () => {
    const pm = props.procedureMilitaire;
    editionForm.value = {
        grade: pm.militaire?.grade || pm.grade_temp || '',
        unite: pm.militaire?.unite || '',
        genre: pm.militaire?.genre || '',
        armee: pm.militaire?.armee || '',
        statut: pm.militaire?.statut || 'Actif',
        date_naissance: pm.militaire?.date_naissance ? 
            new Date(pm.militaire.date_naissance).toISOString().split('T')[0] : ''
    };
    enEdition.value = true;
};

const annulerEdition = () => {
    enEdition.value = false;
};

const sauvegarderInfos = () => {
    saving.value = true;
    const data = {
        grade: editionForm.value.grade,
        unite: editionForm.value.unite,
        genre: editionForm.value.genre,
        armee: editionForm.value.armee,
        statut: editionForm.value.statut,
        date_naissance: editionForm.value.date_naissance
    };
    
    router.patch(route('procedure.militaire.update', { 
        procedure: props.procedureId, 
        procedureMilitaire: props.procedureMilitaire.id 
    }), data, {
        onSuccess: () => {
            saving.value = false;
            enEdition.value = false;
            emit('updated');
        },
        onError: () => {
            saving.value = false;
        }
    });
};

const ouvrirEditionInfractions = () => {
    editInfractionsForm.value = [...(props.procedureMilitaire?.infractions || [])];
    showEditInfractions.value = true;
};

const sauvegarderInfractions = () => {
    savingInfractions.value = true;
    router.patch(route('procedure.militaire.infractions.update', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), { infractions: editInfractionsForm.value }, {
        onSuccess: () => {
            savingInfractions.value = false;
            showEditInfractions.value = false;
            emit('updated');
        },
        onError: () => {
            savingInfractions.value = false;
        }
    });
};

const ouvrirEditionFautes = () => {
    editFautesForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.fautes_militaires || []));
    showEditFautes.value = true;
};

const sauvegarderFautes = () => {
    savingFautes.value = true;
    router.patch(route('procedure.militaire.fautes.update', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), { fautes_militaires: editFautesForm.value }, {
        onSuccess: () => {
            savingFautes.value = false;
            showEditFautes.value = false;
            emit('updated');
        },
        onError: () => {
            savingFautes.value = false;
        }
    });
};

const ouvrirEditionPartiesCiviles = () => {
    editPartiesCivilesForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.parties_civiles || []));
    showEditPartiesCiviles.value = true;
};

const sauvegarderPartiesCiviles = () => {
    savingPartiesCiviles.value = true;
    router.patch(route('procedure.militaire.parties-civiles.update', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), { parties_civiles: editPartiesCivilesForm.value }, {
        onSuccess: () => {
            savingPartiesCiviles.value = false;
            showEditPartiesCiviles.value = false;
            emit('updated');
        },
        onError: () => {
            savingPartiesCiviles.value = false;
        }
    });
};
</script>