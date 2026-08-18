<template>
    <AuthenticatedLayout title="Nouvelle Procédure" subtitle="Créer un dossier judiciaire">
        <!-- ====== AFFICHAGE DES ERREURS ====== -->
        <div v-if="flashError" class="fixed top-4 right-4 z-50 max-w-md bg-red-500 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <i class="pi pi-exclamation-triangle text-lg mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold">Erreur</p>
                    <p class="text-sm whitespace-pre-wrap">{{ flashError }}</p>
                </div>
                <button @click="flashError = null" class="text-white hover:opacity-80">
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>

        <div v-if="flashSuccess" class="fixed top-4 right-4 z-50 max-w-md bg-emerald-500 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <i class="pi pi-check-circle text-lg mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold">Succès</p>
                    <p class="text-sm">{{ flashSuccess }}</p>
                </div>
                <button @click="flashSuccess = null" class="text-white hover:opacity-80">
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>

        <Card class="max-w-5xl mx-auto">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Militaires / Personnels -->
                <div class="border-b border-slate-200 pb-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-4">
                        <i class="pi pi-users mr-2"></i> Personnels concernés
                    </h3>
                    <MilitairesMultiples
                        v-model="form.militaires"
                        v-model:estPlurielle="form.est_plurielle"
                        :infractions="allInfractions"
                        :militairesOptions="optionsMilitaires"
                        :grades="grades"
                        :typePersonnelOptions="typePersonnelOptions"
                        @change="onMilitairesChange"
                        @infraction-created="onInfractionCreated"
                    />
                    <p v-if="form.errors.militaires" class="mt-2 text-sm text-red-500">{{ form.errors.militaires }}</p>
                </div>

                <!-- Phase initiale -->
                <div class="border-b border-slate-200 pb-6">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-4">
                        <i class="pi pi-file mr-2"></i> Phase initiale
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="form.phase_type_id !== 'autre'">
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                Type de phase <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.phase_type_id"
                                required
                                @change="onPhaseTypeChange"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                            >
                                <option value="">Choisir la phase</option>
                                <option v-for="pt in phaseTypes" :key="pt.id" :value="pt.id">
                                    {{ pt.libelle }}
                                </option>
                            </select>
                            <button type="button" @click="form.phase_type_id = 'autre'; onPhaseTypeChange()" class="mt-2 text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium">
                                <i class="pi pi-plus-circle"></i> Créer une phase personnalisée
                            </button>
                            <p v-if="form.errors.phase_type_id" class="mt-1 text-sm text-red-500">{{ form.errors.phase_type_id }}</p>
                        </div>
                        <div v-if="form.phase_type_id === 'autre'">
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                Nom de la phase personnalisée <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="form.phase_personnalisee"
                                    type="text"
                                    required
                                    placeholder="Ex: Rapport d'expertise"
                                    class="flex-1 w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                                />
                                <button type="button" @click="form.phase_type_id = ''; form.phase_personnalisee = ''; onPhaseTypeChange()" class="text-slate-500 hover:text-red-600 p-2 bg-slate-100 rounded-lg hover:bg-red-50" title="Annuler et choisir dans la liste">
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                            <p v-if="form.errors.phase_personnalisee" class="mt-1 text-sm text-red-500">{{ form.errors.phase_personnalisee }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                Date du document <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.date_phase"
                                type="date"
                                required
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                            />
                            <p v-if="form.errors.date_phase" class="mt-1 text-sm text-red-500">{{ form.errors.date_phase }}</p>
                        </div>
                        <!-- Lieu de commission -->
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                Lieu de commission
                            </label>
                            <select
                                v-model="form.lieu_commission"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                            >
                                <option value="">Non défini</option>
                                <option value="Organique">Organique</option>
                                <option value="Operation">Opération</option>
                            </select>
                            <p v-if="form.errors.lieu_commission" class="mt-1 text-sm text-red-500">{{ form.errors.lieu_commission }}</p>
                        </div>
                        <!-- Parquet -->
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">
                                Parquet compétent
                            </label>
                            <div class="flex items-center gap-3 mb-2">
                                <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="aucunParquet"
                                        @change="onToggleAucunParquet"
                                        class="rounded border-slate-400 text-slate-600 focus:ring-slate-500"
                                    />
                                    Aucun parquet
                                </label>
                            </div>
                            <ParquetSelector
                                v-model="form.parquet"
                                :parquets="allParquets"
                                :error="form.errors.parquet"
                                :disabled="aucunParquet"
                                @change="onParquetChange"
                                @error="onParquetError"
                                @parquet-created="onParquetCreated"
                            />
                            <p v-if="form.errors.parquet_type" class="mt-1 text-sm text-red-500">{{ form.errors.parquet_type }}</p>
                            <p v-if="form.errors.parquet_id" class="mt-1 text-sm text-red-500">{{ form.errors.parquet_id }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-800 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Description..."
                            class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500"
                        ></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                    </div>
                    <PhaseFormFields
                        :phaseTypeId="form.phase_type_id"
                        :phaseTypes="phaseTypes"
                        v-model:champs="form.champs"
                        v-model:personnes="form.personnes"
                        v-model:evenements="form.evenements"
                        v-model:references="form.references"
                        v-model:optionsCocher="form.options_cocher"
                        v-model:piecesJointes="form.pieces_jointes"
                    />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <Link :href="route('procedures.index')" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition-colors">
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer"
                    >
                        <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                        Créer la procédure
                    </button>
                </div>
            </form>
        </Card>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';
import PhaseFormFields from '@/Components/Procedure/PhaseFormFields.vue';
import MilitairesMultiples from '@/Components/Procedure/MilitairesMultiples.vue';
import ParquetSelector from '@/Components/Procedure/ParquetSelector.vue';

const page = usePage();

const props = defineProps({
    militaires: Array,
    infractions: Array,
    phaseTypes: Array,
    parquets: Array,
    grades: Array,
    lieuCommissionOptions: Array,
    typePersonnelOptions: Array,
});

// ====== INITIALISATION ======
const optionsMilitaires = ref(props.militaires || []);
const allInfractions = ref(props.infractions || []);
const phaseTypes = ref(props.phaseTypes || []);
const allParquets = ref(props.parquets || []);
const grades = ref(props.grades || []);
const typePersonnelOptions = ref(props.typePersonnelOptions || [
    { value: 'militaire', label: 'Militaire' },
    { value: 'civil', label: 'Civil' }
]);
const aucunParquet = ref(true);

// ====== GESTION DES ERREURS FLASH ======
const flashError = ref(null);
const flashSuccess = ref(null);

watch(() => page.props.flash?.error, (error) => {
    if (error) {
        flashError.value = error;
        setTimeout(() => flashError.value = null, 8000);
    }
}, { immediate: true });

watch(() => page.props.flash?.success, (success) => {
    if (success) {
        flashSuccess.value = success;
        setTimeout(() => flashSuccess.value = null, 5000);
    }
}, { immediate: true });

// ====== FORMULAIRE ======
const form = useForm({
    est_plurielle: false,
    militaires: [
        {
            type_personnel: 'militaire',
            militaire_id: null,
            nom: '',
            prenom: '',
            profession: '',
            grade: '',
            grade_id: '',
            matricule: '',
            infractions: [],
            fautes_militaires: [],
            parties_civiles: [],
            est_nouveau: true
        }
    ],
    phase_type_id: '',
    phase_personnalisee: '',
    date_phase: '',
    description: '',
    lieu_commission: '',
    parquet: {
        type: 'militaire',
        id: null,
        nom: '',
        localisation: '',
        code: ''
    },
    champs: [],
    personnes: [],
    evenements: [],
    references: [],
    options_cocher: [],
    pieces_jointes: [],
});

// ====== GESTION DE "AUCUN PARQUET" ======
const onToggleAucunParquet = () => {
    if (aucunParquet.value) {
        form.parquet = {
            type: 'militaire',
            id: null,
            nom: '',
            localisation: '',
            code: ''
        };
    }
};

// ====== WATCH DES ERREURS ======
watch(() => form.errors, (errors) => {
    if (errors && Object.keys(errors).length > 0) {
        let errorMessage = '';
        Object.keys(errors).forEach(key => {
            if (Array.isArray(errors[key])) {
                errorMessage += `${key}: ${errors[key].join(', ')}\n`;
            } else {
                errorMessage += `${key}: ${errors[key]}\n`;
            }
        });
        flashError.value = errorMessage;
        setTimeout(() => flashError.value = null, 8000);
    }
}, { deep: true });

// ====== FONCTIONS ======
const onMilitairesChange = (militaires) => {
    console.log('🔄 Personnels mis à jour:', militaires);
};

const onParquetChange = (value) => {
    console.log('🔄 Parquet mis à jour:', value);
    form.parquet = value;
    if (value && (value.id || value.nom)) {
        aucunParquet.value = false;
    }
};

const onParquetError = (error) => {
    if (error) {
        form.errors.parquet = error;
    } else {
        delete form.errors.parquet;
    }
};

const onParquetCreated = (newParquet) => {
    allParquets.value.push(newParquet);
    console.log('✅ Nouveau parquet créé:', newParquet);
    fetch('/api/parquets')
        .then(r => r.json())
        .then(data => {
            allParquets.value = data;
        })
        .catch(err => console.error('Erreur chargement parquets:', err));
};

const onInfractionCreated = (newInfraction) => {
    allInfractions.value.push(newInfraction);
    console.log('✅ Nouvelle infraction créée:', newInfraction);
};

const onPhaseTypeChange = () => {
    form.champs = [];
    form.personnes = [];
    form.evenements = [];
    form.references = [];
    form.options_cocher = [];
    form.pieces_jointes = [{ nom: '', description: '', contexte: '' }];

    const tid = form.phase_type_id;
    if (tid && tid !== 'autre') {
        const pt = phaseTypes.value.find(p => p.id == tid);
        if (pt) {
            if (pt.slug === 'communique') {
                form.personnes = [{ nom: '', prenom: '', profession: '', autre: '' }];
                form.evenements = [{ nom: '', date_evenement: '', description: '' }];
                form.champs = [
                    { cle: 'origine', valeur: '', type: 'text' },
                    { cle: 'numero', valeur: '', type: 'text' },
                    { cle: 'date_communique', valeur: form.date_phase, type: 'date' }
                ];
            } else if (pt.slug === 'mise_a_disposition') {
                form.references = [{ libelle: '', description: '' }];
                form.champs = [{ cle: 'date_mad', valeur: form.date_phase, type: 'date' }];
            } else if (pt.slug === 'ordre_de_poursuite') {
                form.options_cocher = [
                    { libelle: 'Détenu', est_coche: false },
                    { libelle: 'Non détenu', est_coche: false },
                    { libelle: 'Citation directe', est_coche: false },
                    { libelle: 'Information', est_coche: false },
                    { libelle: 'Autre', est_coche: false }
                ];
                form.champs = [
                    { cle: 'reglement', valeur: '', type: 'text' },
                    { cle: 'ordonnance_juge_instruction', valeur: '', type: 'text' },
                    { cle: 'jugement', valeur: '', type: 'text' },
                    { cle: 'peine', valeur: '', type: 'text' },
                    { cle: 'voix_de_recours', valeur: '', type: 'text' },
                    { cle: 'arret_rendu', valeur: '', type: 'text' },
                    { cle: 'voix_recours_arret', valeur: '', type: 'text' }
                ];
            }
        }
    }
};

watch(() => form.date_phase, (val) => {
    form.champs.forEach(c => {
        if (c.type === 'date' && !c.valeur) c.valeur = val;
    });
});

// ====== SOUMISSION ======
const submit = () => {
    flashError.value = null;
    flashSuccess.value = null;
    
    const parquetType = form.parquet.type || 'militaire';
    const parquetId = form.parquet.id || null;
    const parquetNom = form.parquet.nom || '';
    
    console.log('📤 Vérification parquet:', { type: parquetType, id: parquetId, nom: parquetNom });
    
    let finalParquetId = parquetId;
    let finalParquetNom = parquetNom;
    let finalParquetType = parquetType;
    
    if (aucunParquet.value) {
        finalParquetId = null;
        finalParquetNom = '';
        finalParquetType = '';
    }
    
    const data = {
        est_plurielle: form.est_plurielle,
        militaires: form.militaires,
        phase_type_id: form.phase_type_id,
        phase_personnalisee: form.phase_personnalisee,
        date_phase: form.date_phase,
        description: form.description,
        lieu_commission: form.lieu_commission || null,
        champs: form.champs,
        personnes: form.personnes,
        evenements: form.evenements,
        references: form.references,
        options_cocher: form.options_cocher,
        pieces_jointes: form.pieces_jointes,
        parquet_type: finalParquetType,
        parquet_id: finalParquetId,
        parquet_nom: finalParquetNom,
        parquet_localisation: form.parquet.localisation || '',
        parquet_code: form.parquet.code || '',
        aucun_parquet: aucunParquet.value,
    };
    
    console.log('📤 Données envoyées:', data);
    
    form.transform((formData) => ({
        ...formData,
        parquet_type: finalParquetType,
        parquet_id: finalParquetId,
        parquet_nom: finalParquetNom,
        parquet_localisation: form.parquet.localisation || '',
        parquet_code: form.parquet.code || '',
        aucun_parquet: aucunParquet.value,
    })).post(route('procedures.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            flashSuccess.value = 'Procédure créée avec succès!';
            fetch('/api/phase-types')
                .then(r => r.json())
                .then(data => { phaseTypes.value = data; })
                .catch(err => console.error('Erreur chargement phase types:', err));
            fetch('/api/parquets')
                .then(r => r.json())
                .then(data => { allParquets.value = data; })
                .catch(err => console.error('Erreur chargement parquets:', err));
        },
        onError: (errors) => {
            console.error('❌ Erreurs:', errors);
            let errorMessage = '';
            Object.keys(errors).forEach(key => {
                if (Array.isArray(errors[key])) {
                    errorMessage += `${key}: ${errors[key].join(', ')}\n`;
                } else {
                    errorMessage += `${key}: ${errors[key]}\n`;
                }
            });
            flashError.value = errorMessage || 'Une erreur est survenue';
            setTimeout(() => flashError.value = null, 10000);
        }
    });
};
</script>
<script>export default { layout: null };</script>