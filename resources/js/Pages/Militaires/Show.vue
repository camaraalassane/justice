<template>
    <AuthenticatedLayout title="Fiche Personnel" :subtitle="`${personnel.matricule || 'Sans matricule'} - ${personnel.nom || 'Nom inconnu'} ${personnel.prenoms || ''}`">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Infos personnelles -->
            <Card>
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center text-gpj-600 font-bold text-2xl mx-auto mb-3">
                        {{ personnel.nom ? personnel.nom.charAt(0) : '?' }}{{ personnel.prenoms ? personnel.prenoms.charAt(0) : '' }}
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800">{{ personnel.nom || 'Nom inconnu' }} {{ personnel.prenoms || '' }}</h3>
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-1">
                        <Badge :variant="statutVariant(personnel.statut)">{{ personnel.statut || 'Non défini' }}</Badge>
                        <Badge :variant="personnel.type_personnel === 'militaire' ? 'info' : 'primary'" size="sm">
                            {{ personnel.type_personnel === 'militaire' ? 'Militaire' : 'Civil' }}
                        </Badge>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Type</span>
                        <span class="font-medium text-gpj-800">{{ personnel.type_personnel === 'militaire' ? 'Militaire' : 'Civil' }}</span>
                    </div>
                    <div v-if="personnel.type_personnel === 'militaire'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Matricule</span>
                        <span class="font-medium text-gpj-800">{{ personnel.matricule || 'Non défini' }}</span>
                    </div>
                    <div v-if="personnel.type_personnel === 'militaire'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Grade</span>
                        <span class="font-medium text-gpj-800">{{ personnel.grade?.libelle || personnel.grade || '-' }}</span>
                    </div>
                    <div v-if="personnel.type_personnel === 'militaire'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Catégorie</span>
                        <span class="font-medium text-gpj-800">{{ personnel.grade?.categorie?.libelle || '-' }}</span>
                    </div>
                    <div v-if="personnel.type_personnel === 'civil'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Profession</span>
                        <span class="font-medium text-gpj-800">{{ personnel.profession || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Genre</span>
                        <span class="font-medium text-gpj-800">{{ personnel.genre || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Date de naissance</span>
                        <span class="font-medium text-gpj-800">{{ formatDate(personnel.date_naissance) || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Lieu de naissance</span>
                        <span class="font-medium text-gpj-800">{{ personnel.lieu_naissance || '-' }}</span>
                    </div>
                    
                    <!-- Filiation - Père -->
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Père</span>
                        <span class="font-medium text-gpj-800 text-right">
                            {{ filiationPere || '-' }}
                        </span>
                    </div>
                    
                    <!-- Filiation - Mère -->
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Mère</span>
                        <span class="font-medium text-gpj-800 text-right">
                            {{ filiationMere || '-' }}
                        </span>
                    </div>
                    
                    <div v-if="personnel.type_personnel === 'militaire'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Armée/Service</span>
                        <span class="font-medium text-gpj-800">{{ armeeNom || '-' }}</span>
                    </div>
                    <div v-if="personnel.type_personnel === 'militaire'" class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Unité</span>
                        <span class="font-medium text-gpj-800">{{ personnel.unite || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Adresse</span>
                        <span class="font-medium text-gpj-800">{{ personnel.adresse || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Téléphone</span>
                        <span class="font-medium text-gpj-800">{{ personnel.telephone || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-200">
                        <span class="text-gpj-400">Statut</span>
                        <Badge :variant="statutVariant(personnel.statut)" size="sm">{{ personnel.statut || 'Non défini' }}</Badge>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-4 space-y-2">
                    <a
                        v-if="personnel.type_personnel === 'militaire'"
                        :href="route('militaires.casier', personnel.id)"
                        target="_blank"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors"
                    >
                        <i class="pi pi-print"></i> Imprimer Casier Judiciaire
                    </a>
                    <Link
                        :href="route('militaires.edit', personnel.id)"
                        class="w-full flex items-center justify-center gap-2 px-3 py-2.5 border border-slate-300 text-gpj-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors"
                    >
                        <i class="pi pi-pencil"></i> Modifier
                    </Link>
                </div>
            </Card>

            <!-- Procédures -->
            <div class="lg:col-span-2">
                <Card title="Procédures Judiciaires">
                    <div v-if="personnel.procedures?.length" class="space-y-3">
                        <Link
                            v-for="procedure in personnel.procedures"
                            :key="procedure.id"
                            :href="route('procedures.show', procedure.id)"
                            class="flex items-center gap-4 p-4 rounded-lg border border-slate-300 hover:border-slate-500 hover:shadow-sm transition-all"
                        >
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                <i class="pi pi-folder text-gpj-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gpj-800">{{ procedure.numero_procedure }}</p>
                                <p class="text-xs text-gpj-400">{{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || 'Date inconnue' }}</p>
                                <p v-if="procedure.parquet" class="text-xs text-gpj-400">
                                    <i class="pi pi-building mr-1"></i> {{ procedure.parquet?.nom || '-' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <Badge v-for="inf in procedure.infractions?.slice(0, 2)" :key="inf.id" variant="default" size="sm">
                                    {{ inf.libelle }}
                                </Badge>
                                <Badge v-if="procedure.infractions?.length > 2" variant="neutral" size="sm">
                                    +{{ procedure.infractions.length - 2 }}
                                </Badge>
                            </div>
                            <Badge :variant="phaseVariant(procedure.phase)" size="sm">{{ (procedure.phase || '').replace(/_/g, ' ') || 'En cours' }}</Badge>
                        </Link>
                    </div>
                    <p v-else class="text-center text-gpj-400 py-8">
                        <i class="pi pi-inbox text-2xl mb-2 block"></i>
                        Aucune procédure
                    </p>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const props = defineProps({
    militaire: {
        type: Object,
        required: true
    },
});

// Alias pour éviter la confusion
const personnel = props.militaire;

const formatDate = (d) => {
    if (!d) return null;
    const date = new Date(d);
    if (isNaN(date.getTime())) return null;
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const statutVariant = (s) => {
    const m = { 
        'En activité': 'success', 
        'Non activite': 'warning', 
        'En retraite': 'info', 
        'Radié': 'neutral' 
    };
    return m[s] || 'default';
};

const phaseVariant = (p) => {
    const m = { 
        'Ordre de Poursuite': 'warning', 
        'Mise à Disposition': 'danger', 
        'Communiqué': 'info', 
        'Brouillon': 'neutral',
        'Instruction': 'info',
        'Jugement': 'success'
    };
    return m[p] || 'default';
};

// Computed pour la filiation complète
const filiationPere = computed(() => {
    if (personnel.prenoms_pere && personnel.nom_pere) {
        return `${personnel.prenoms_pere} ${personnel.nom_pere}`;
    }
    if (personnel.nom_pere) {
        return personnel.nom_pere;
    }
    return null;
});

const filiationMere = computed(() => {
    if (personnel.prenoms_mere && personnel.nom_mere) {
        return `${personnel.prenoms_mere} ${personnel.nom_mere}`;
    }
    if (personnel.nom_mere) {
        return personnel.nom_mere;
    }
    return null;
});

// Computé pour l'armée
const armeeNom = computed(() => {
    return personnel.armee_relation?.nom || personnel.armee || '-';
});
</script>

<script>export default { layout: null };</script>