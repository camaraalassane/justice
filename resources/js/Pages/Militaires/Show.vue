<template>
    <AuthenticatedLayout title="Fiche Militaire" :subtitle="`${militaire.matricule || 'Sans matricule'} - ${militaire.nom || 'Nom inconnu'} ${militaire.prenoms || ''}`">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Infos militaires -->
            <Card>
                <div class="text-center mb-4">
                    <div class="w-20 h-20 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-600 font-bold text-2xl mx-auto mb-3">
                        {{ militaire.nom ? militaire.nom.charAt(0) : '?' }}{{ militaire.prenoms ? militaire.prenoms.charAt(0) : '' }}
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800">{{ militaire.nom || 'Nom inconnu' }} {{ militaire.prenoms || '' }}</h3>
                    <Badge :variant="statutVariant(militaire.statut)" class="mt-1">{{ militaire.statut || 'Non défini' }}</Badge>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Matricule</span>
                        <span class="font-medium text-gpj-800">{{ militaire.matricule || 'Non défini' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Grade</span>
                        <span class="font-medium text-gpj-800">{{ militaire.grade?.libelle || militaire.grade || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Catégorie</span>
                        <span class="font-medium text-gpj-800">{{ militaire.grade?.categorie?.libelle || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Genre</span>
                        <span class="font-medium text-gpj-800">{{ militaire.genre || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Date de naissance</span>
                        <span class="font-medium text-gpj-800">{{ formatDate(militaire.date_naissance) || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Armée/Service</span>
                        <span class="font-medium text-gpj-800">{{ militaire.armee || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Unité</span>
                        <span class="font-medium text-gpj-800">{{ militaire.unite || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Adresse</span>
                        <span class="font-medium text-gpj-800">{{ militaire.adresse || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gpj-100">
                        <span class="text-gpj-400">Téléphone</span>
                        <span class="font-medium text-gpj-800">{{ militaire.telephone || '-' }}</span>
                    </div>
                </div>
                <a
                    :href="route('militaires.casier', militaire.id)"
                    target="_blank"
                    class="mt-4 w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors"
                >
                    <i class="pi pi-print"></i> Imprimer Casier Judiciaire
                </a>
            </Card>

            <!-- Procédures -->
            <div class="lg:col-span-2">
                <Card title="Procédures Judiciaires">
                    <div v-if="militaire.procedures?.length" class="space-y-3">
                        <Link
                            v-for="procedure in militaire.procedures"
                            :key="procedure.id"
                            :href="route('procedures.show', procedure.id)"
                            class="flex items-center gap-4 p-4 rounded-lg border border-gpj-200 hover:border-gpj-400 hover:shadow-sm transition-all"
                        >
                            <div class="w-10 h-10 rounded-lg bg-gpj-100 flex items-center justify-center shrink-0">
                                <i class="pi pi-folder text-gpj-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gpj-800">{{ procedure.numero_procedure }}</p>
                                <p class="text-xs text-gpj-400">{{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || 'Date inconnue' }}</p>
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
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const props = defineProps({
    militaire: Object,
});

const formatDate = (d) => {
    if (!d) return null;
    const date = new Date(d);
    if (isNaN(date.getTime())) return null;
    return date.toLocaleDateString('fr-FR');
};

const statutVariant = (s) => {
    const m = { Actif: 'success', Suspendu: 'warning', Déserteur: 'danger', Radié: 'neutral' };
    return m[s] || 'default';
};

const phaseVariant = (p) => {
    const m = { 
        'Ordre de Poursuite': 'warning', 
        'Mise à Disposition': 'danger', 
        'Communiqué': 'info', 
        'Brouillon': 'neutral' 
    };
    return m[p] || 'default';
};
</script>

<script>export default { layout: null };</script>