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
                <!-- Militaires -->
                <div class="border-b border-gpj-100 pb-6">
                    <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide mb-4">
                        <i class="pi pi-users mr-2"></i> Militaires concernés
                    </h3>
                    <MilitairesMultiples
                        v-model="form.militaires"
                        v-model:estPlurielle="form.est_plurielle"
                        :infractions="allInfractions"
                        :militairesOptions="optionsMilitaires"
                        @change="onMilitairesChange"
                    />
                    <p v-if="form.errors.militaires" class="mt-2 text-sm text-red-500">{{ form.errors.militaires }}</p>
                </div>

                <!-- Phase initiale -->
                <div class="border-b border-gpj-100 pb-6">
                    <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide mb-4">
                        <i class="pi pi-file mr-2"></i> Phase initiale
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Type de phase <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.phase_type_id"
                                required
                                @change="onPhaseTypeChange"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            >
                                <option value="">Choisir la phase</option>
                                <option v-for="pt in phaseTypes" :key="pt.id" :value="pt.id">
                                    {{ pt.libelle }}
                                </option>
                                <option value="autre">--- Ajouter une autre ---</option>
                            </select>
                            <p v-if="form.errors.phase_type_id" class="mt-1 text-sm text-red-500">{{ form.errors.phase_type_id }}</p>
                        </div>
                        <div v-if="form.phase_type_id === 'autre'">
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Nom de la phase <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.phase_personnalisee"
                                type="text"
                                required
                                placeholder="Nom personnalisé"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            />
                            <p v-if="form.errors.phase_personnalisee" class="mt-1 text-sm text-red-500">{{ form.errors.phase_personnalisee }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Date du document <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.date_phase"
                                type="date"
                                required
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            />
                            <p v-if="form.errors.date_phase" class="mt-1 text-sm text-red-500">{{ form.errors.date_phase }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Parquet compétent <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.parquet_competent"
                                required
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            >
                                <option value="">Choisir</option>
                                <option v-for="p in parquets" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <p v-if="form.errors.parquet_competent" class="mt-1 text-sm text-red-500">{{ form.errors.parquet_competent }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Description</label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Description..."
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
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
                    <Link :href="route('procedures.index')" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 transition-colors">
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

const page = usePage();

const props = defineProps({
    militaires: Array,
    infractions: Array,
    phaseTypes: Array,
    parquets: Array
});

// ====== INITIALISATION DES RÉFÉRENCES ======
const optionsMilitaires = ref(props.militaires || []);
const allInfractions = ref(props.infractions || []);
const phaseTypes = ref(props.phaseTypes || []);

// ====== GESTION DES ERREURS FLASH ======
const flashError = ref(null);
const flashSuccess = ref(null);

// Écouter les erreurs flash
watch(() => page.props.flash?.error, (error) => {
    if (error) {
        flashError.value = error;
        console.error('❌ Erreur flash:', error);
        setTimeout(() => flashError.value = null, 8000);
    }
}, { immediate: true });

watch(() => page.props.flash?.success, (success) => {
    if (success) {
        flashSuccess.value = success;
        console.log('✅ Succès:', success);
        setTimeout(() => flashSuccess.value = null, 5000);
    }
}, { immediate: true });

// ====== FORMULAIRE ======
const form = useForm({
    est_plurielle: false,
    militaires: [
        {
            militaire_id: null,
            nom: '',
            prenom: '',
            grade: '',
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
    parquet_competent: '',
    champs: [],
    personnes: [],
    evenements: [],
    references: [],
    options_cocher: [],
    pieces_jointes: [],
});

// ====== WATCH DES ERREURS DE VALIDATION ======
watch(() => form.errors, (errors) => {
    if (errors && Object.keys(errors).length > 0) {
        console.error('❌ Erreurs de validation:', errors);
        let errorMessage = '';
        Object.keys(errors).forEach(key => {
            if (Array.isArray(errors[key])) {
                errorMessage += `${key}: ${errors[key].join(', ')}\n`;
            } else {
                errorMessage += `${key}: ${errors[key]}\n`;
            }
        });
        flashError.value = errorMessage || 'Erreurs de validation';
        setTimeout(() => flashError.value = null, 8000);
    }
}, { deep: true });

// ====== FONCTIONS ======
const onMilitairesChange = (militaires) => {
    console.log('🔄 Militaires mis à jour:', militaires);
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
    console.log('📤 Envoi des données:', form.data());
    
    flashError.value = null;
    flashSuccess.value = null;
    
    form.post(route('procedures.store'), {
        preserveScroll: true,
        onStart: () => {
            console.log('⏳ Début de la requête...');
        },
        onSuccess: (response) => {
            console.log('✅ Succès!', response);
            flashSuccess.value = 'Procédure créée avec succès!';
            
            fetch('/api/phase-types')
                .then(r => r.json())
                .then(data => {
                    phaseTypes.value = data;
                })
                .catch(err => console.error('Erreur chargement phase types:', err));
        },
        onError: (errors) => {
            console.error('❌ Erreurs:', errors);
            
            let errorMessage = '';
            if (typeof errors === 'string') {
                errorMessage = errors;
            } else if (errors.error) {
                errorMessage = errors.error;
            } else if (errors.message) {
                errorMessage = errors.message;
            } else if (typeof errors === 'object') {
                Object.keys(errors).forEach(key => {
                    if (Array.isArray(errors[key])) {
                        errorMessage += `${key}: ${errors[key].join(', ')}\n`;
                    } else if (typeof errors[key] === 'string') {
                        errorMessage += `${key}: ${errors[key]}\n`;
                    } else if (typeof errors[key] === 'object') {
                        errorMessage += `${key}: ${JSON.stringify(errors[key])}\n`;
                    }
                });
            }
            
            if (!errorMessage) {
                errorMessage = 'Une erreur est survenue lors de la création de la procédure. Vérifiez les logs.';
            }
            
            flashError.value = errorMessage;
            setTimeout(() => flashError.value = null, 10000);
        },
        onFinish: () => {
            console.log('🏁 Requête terminée');
        }
    });
};
</script>
<script>export default { layout: null };</script>