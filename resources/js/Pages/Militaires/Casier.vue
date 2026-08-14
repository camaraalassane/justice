<template>
    <AuthenticatedLayout :title="'Casier Judiciaire'" :subtitle="militaire.nom + ' ' + militaire.prenoms">
        <div class="space-y-6">
            <!-- En-tête avec boutons d'action -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-400 text-xl font-bold">
                        {{ getInitiales() }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ militaire.nom }} {{ militaire.prenoms }}</h1>
                        <div class="flex items-center gap-3 flex-wrap text-sm text-slate-600">
                            <span><span class="text-slate-500">Matricule:</span> {{ militaire.matricule || 'Non défini' }}</span>
                            <span><span class="text-slate-500">Grade:</span> {{ militaire.grade?.libelle || 'Non renseigné' }}</span>
                            <span><span class="text-slate-500">Statut:</span> <Badge :variant="statutVariant(militaire.statut)" size="sm">{{ militaire.statut || 'Non renseigné' }}</Badge></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('militaires.show', militaire.id)" class="px-3 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-1">
                        <i class="pi pi-arrow-left text-xs"></i> Retour
                    </Link>
                    <a :href="route('militaires.casier.pdf', militaire.id)" target="_blank" class="px-3 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-2">
                        <i class="pi pi-download"></i> Exporter PDF
                    </a>
                    <button v-if="peutModifier" @click="printCasier" class="px-3 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-2">
                        <i class="pi pi-print"></i> Imprimer
                    </button>
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard label="Total Procédures" :value="procedures.length" icon="pi pi-folder" icon-bg="bg-slate-100" icon-color="#2d5a3d" />
                <StatCard label="En cours" :value="proceduresEnCours.length" icon="pi pi-clock" icon-bg="bg-amber-100" icon-color="#b45309" />
                <StatCard label="Condamnations" :value="condamnations.length" icon="pi pi-gavel" icon-bg="bg-red-100" icon-color="#dc2626" />
                <StatCard label="Acquittements" :value="getAcquittements().length" icon="pi pi-check-circle" icon-bg="bg-emerald-100" icon-color="#059669" />
            </div>

            <!-- Section Identité -->
            <Card title="I - IDENTITÉ DU MILITAIRE">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div><p class="text-slate-500">Matricule</p><p class="font-medium">{{ militaire.matricule || 'Non défini' }}</p></div>
                    <div><p class="text-slate-500">Grade</p><p class="font-medium">{{ militaire.grade?.libelle || militaire.grade || 'Non renseigné' }}</p></div>
                    <div><p class="text-slate-500">Nom</p><p class="font-medium">{{ militaire.nom || 'Inconnu' }}</p></div>
                    <div><p class="text-slate-500">Prénoms</p><p class="font-medium">{{ militaire.prenoms || 'Inconnu' }}</p></div>
                    <div><p class="text-slate-500">Date de naissance</p><p class="font-medium">{{ formatDate(militaire.date_naissance) }}</p></div>
                    <div><p class="text-slate-500">Lieu de naissance</p><p class="font-medium">{{ militaire.lieu_naissance || 'Non renseigné' }}</p></div>
                    <div><p class="text-slate-500">Genre</p><p class="font-medium">{{ militaire.genre || 'Non renseigné' }}</p></div>
                    <div><p class="text-slate-500">Statut</p><Badge :variant="statutVariant(militaire.statut)" size="sm">{{ militaire.statut || 'Non renseigné' }}</Badge></div>
                    <div><p class="text-slate-500">Filiation Père</p><p class="font-medium">{{ getFiliationPere() }}</p></div>
                    <div><p class="text-slate-500">Filiation Mère</p><p class="font-medium">{{ getFiliationMere() }}</p></div>
                    <div><p class="text-slate-500">Armée/Service</p><p class="font-medium">{{ militaire.armee || militaire.armee_relation?.nom || 'Non renseigné' }}</p></div>
                    <div><p class="text-slate-500">Unité</p><p class="font-medium">{{ militaire.unite || 'Non renseignée' }}</p></div>
                </div>
            </Card>

            <!-- Section II - Procédures en cours -->
            <Card title="II - PROCÉDURES EN COURS">
                <div v-if="proceduresEnCours.length > 0" class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">N° Procédure</th>
                                <th class="px-3 py-2 text-left font-semibold">Date ouverture</th>
                                <th class="px-3 py-2 text-left font-semibold">Phase actuelle</th>
                                <th class="px-3 py-2 text-left font-semibold">Parquet</th>
                                <th class="px-3 py-2 text-left font-semibold">Infraction(s)</th>
                                <th class="px-3 py-2 text-left font-semibold">Autres accusés</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="proc in proceduresEnCours" :key="proc.id" class="hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <Link :href="route('procedures.show', proc.id)" class="text-slate-600 hover:underline">
                                        {{ proc.numero_procedure }}
                                    </Link>
                                </td>
                                <td class="px-3 py-2">{{ formatDate(proc.date_ouverture) }}</td>
                                <td class="px-3 py-2">{{ (proc.phase || 'En cours').replace(/_/g, ' ') }}</td>
                                <td class="px-3 py-2">
                                    <span v-if="proc.parquet">
                                        {{ proc.parquet.nom }}
                                        <span class="text-[10px] text-slate-500">({{ proc.parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun' }})</span>
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="inf in getInfractionsForProcedure(proc)" :key="inf.id" variant="neutral" size="sm" class="text-[10px]">
                                            {{ inf.libelle }}
                                        </Badge>
                                        <span v-if="getInfractionsForProcedure(proc).length === 0" class="text-slate-500">-</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div v-if="proc.est_plurielle && proc.procedure_militaires?.length > 1">
                                        <span class="text-[10px] bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">Pluriel</span>
                                        <div class="text-xs text-slate-600 mt-1">
                                            <div v-for="pm in proc.procedure_militaires" :key="pm.id">
                                                <span v-if="pm.militaire_id !== militaire.id">
                                                    - {{ pm.militaire?.nom || pm.nom_temp || 'Inconnu' }}
                                                    {{ pm.militaire?.prenoms || pm.prenom_temp || '' }}
                                                    <span v-if="pm.militaire_id === proc.militaire_id" class="text-[10px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full">Principal</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune procédure en cours</p>
            </Card>

            <!-- Section III - Condamnations -->
            <Card title="III - CONDAMNATIONS">
                <div v-if="condamnations.length > 0" class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">N° Procédure</th>
                                <th class="px-3 py-2 text-left font-semibold">Date condamnation</th>
                                <th class="px-3 py-2 text-left font-semibold">Juridiction</th>
                                <th class="px-3 py-2 text-left font-semibold">Infraction(s)</th>
                                <th class="px-3 py-2 text-left font-semibold">Peine</th>
                                <th class="px-3 py-2 text-left font-semibold">Autres accusés</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="cond in condamnations" :key="cond.id" class="hover:bg-slate-50">
                                <td class="px-3 py-2">
                                    <Link :href="route('procedures.show', cond.id)" class="text-slate-600 hover:underline">
                                        {{ cond.numero_procedure }}
                                    </Link>
                                </td>
                                <td class="px-3 py-2">{{ formatDate(cond.date_condamnation || cond.date_ouverture) }}</td>
                                <td class="px-3 py-2">
                                    <span v-if="cond.parquet">
                                        {{ cond.parquet.nom }}
                                        <span class="text-[10px] text-slate-500">({{ cond.parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun' }})</span>
                                    </span>
                                    <span v-else-if="cond.jugement?.juridiction">{{ cond.jugement.juridiction }}</span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="inf in getInfractionsForProcedure(cond)" :key="inf.id" variant="neutral" size="sm" class="text-[10px]">
                                            {{ inf.libelle }}
                                        </Badge>
                                        <span v-if="getInfractionsForProcedure(cond).length === 0" class="text-slate-500">-</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div v-if="cond.peine_principale || cond.jugement?.peine_principale">
                                        <span class="text-red-600 font-medium">{{ cond.peine_principale || cond.jugement?.peine_principale }}</span>
                                        <span v-if="cond.peine_description || cond.jugement?.peines_complementaires" class="text-xs text-slate-600 block">
                                            {{ cond.peine_description || cond.jugement?.peines_complementaires }}
                                        </span>
                                        <span class="text-[10px] bg-red-500 text-white px-2 py-0.5 rounded-full mt-1 inline-block">Condamné</span>
                                    </div>
                                    <span v-else class="text-[10px] bg-red-500 text-white px-2 py-0.5 rounded-full">Condamné</span>
                                </td>
                                <td class="px-3 py-2">
                                    <div v-if="cond.est_plurielle && cond.procedure_militaires?.length > 1">
                                        <span class="text-[10px] bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">Pluriel</span>
                                        <div class="text-xs text-slate-600 mt-1">
                                            <div v-for="pm in cond.procedure_militaires" :key="pm.id">
                                                <span v-if="pm.militaire_id !== militaire.id">
                                                    - {{ pm.militaire?.nom || pm.nom_temp || 'Inconnu' }}
                                                    {{ pm.militaire?.prenoms || pm.prenom_temp || '' }}
                                                    <span v-if="pm.militaire_id === cond.militaire_id" class="text-[10px] bg-emerald-100 text-emerald-600 px-1.5 py-0.5 rounded-full">Principal</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune condamnation</p>
            </Card>

            <!-- Section IV - Acquittements -->
            <Card v-if="getAcquittements().length > 0" title="IV - ACQUITTEMENTS">
                <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">N° Jugement</th>
                                <th class="px-3 py-2 text-left font-semibold">Date jugement</th>
                                <th class="px-3 py-2 text-left font-semibold">Juridiction</th>
                                <th class="px-3 py-2 text-left font-semibold">Infraction(s)</th>
                                <th class="px-3 py-2 text-left font-semibold">Motif</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="proc in getAcquittements()" :key="proc.id" class="hover:bg-slate-50">
                                <td class="px-3 py-2">{{ proc.jugement?.numero_jugement || 'Non défini' }}</td>
                                <td class="px-3 py-2">{{ formatDate(proc.jugement?.date_jugement) }}</td>
                                <td class="px-3 py-2">{{ proc.jugement?.juridiction || 'Non définie' }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="inf in getInfractionsForProcedure(proc)" :key="inf.id" variant="neutral" size="sm" class="text-[10px]">
                                            {{ inf.libelle }}
                                        </Badge>
                                        <span v-if="getInfractionsForProcedure(proc).length === 0" class="text-slate-500">-</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2">{{ proc.jugement?.motif_acquittement || 'Non précisé' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <!-- Section V - Récapitulatif -->
            <Card title="V - RÉCAPITULATIF">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-3 bg-slate-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-slate-700">{{ procedures.length }}</p>
                        <p class="text-xs text-slate-500">Total procédures</p>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-amber-600">{{ proceduresEnCours.length }}</p>
                        <p class="text-xs text-slate-500">Procédures en cours</p>
                    </div>
                    <div class="p-3 bg-red-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-red-600">{{ condamnations.length }}</p>
                        <p class="text-xs text-slate-500">Condamnations</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-lg text-center">
                        <p class="text-2xl font-bold text-emerald-600">{{ getAcquittements().length }}</p>
                        <p class="text-xs text-slate-500">Acquittements</p>
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';
import StatCard from '@/Components/GPJ/StatCard.vue';

const props = defineProps({
    militaire: Object,
    procedures: { type: Array, default: () => [] },
    proceduresEnCours: { type: Array, default: () => [] },
    condamnations: { type: Array, default: () => [] },
    peutModifier: { type: Boolean, default: false },
});

const getInitiales = () => {
    const m = props.militaire;
    return (m.nom?.charAt(0) || '') + (m.prenoms?.charAt(0) || '');
};

const formatDate = (date) => {
    if (!date) return 'Non définie';
    const d = new Date(date);
    if (isNaN(d.getTime())) return 'Non définie';
    return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const statutVariant = (statut) => {
    const map = {
        'En activité': 'success',
        'Non activite': 'warning',
        'Non activité': 'warning',
        'En retraite': 'info',
        'Radié': 'neutral',
    };
    return map[statut] || 'default';
};

const getFiliationPere = () => {
    const m = props.militaire;
    if (m.prenoms_pere && m.nom_pere) return `${m.prenoms_pere} ${m.nom_pere}`;
    if (m.nom_pere) return m.nom_pere;
    return 'Non renseigné';
};

const getFiliationMere = () => {
    const m = props.militaire;
    if (m.prenoms_mere && m.nom_mere) return `${m.prenoms_mere} ${m.nom_mere}`;
    if (m.nom_mere) return m.nom_mere;
    return 'Non renseigné';
};

const getInfractionsForProcedure = (procedure) => {
    if (procedure.infractions_pivot_models) {
        return procedure.infractions_pivot_models;
    }
    if (procedure.infractions) {
        return procedure.infractions;
    }
    return [];
};

const getAcquittements = () => {
    return props.procedures.filter(p => p.jugement && p.jugement.verdict === 'Acquittement');
};

const printCasier = () => {
    window.print();
};
</script>

<style scoped>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>