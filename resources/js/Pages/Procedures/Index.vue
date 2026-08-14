<template>
    <AuthenticatedLayout title="Procédures Judiciaires" subtitle="Gestion et suivi des dossiers">
        <div v-if="flashMessage" :class="['fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 max-w-xs sm:max-w-sm', flashMessage.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white']">
            <i :class="flashMessage.type === 'success' ? 'pi pi-check-circle' : 'pi pi-exclamation-circle'" class="text-sm"></i>
            <span class="flex-1 truncate">{{ flashMessage.message }}</span>
            <button @click="flashMessage = null" class="shrink-0 hover:opacity-80"><i class="pi pi-times text-xs"></i></button>
        </div>

        <div class="space-y-3 sm:space-y-4">
            <!-- Filtres -->
            <Card padding>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 sm:gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Phase</label>
                        <select v-model="filtres.phase" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Toutes</option>
                            <option value="Ordre de Poursuite">Ordre de Poursuite</option>
                            <option value="Mise à Disposition">Mise à Disposition</option>
                            <option value="Communiqué">Communiqué</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Type personnel</label>
                        <select v-model="filtres.type_personnel" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Tous</option>
                            <option value="militaire">Militaire</option>
                            <option value="civil">Civil</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Mois</label>
                        <select v-model="filtres.mois" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Tous</option>
                            <option v-for="mois in moisOptions" :key="mois.value" :value="mois.value">{{ mois.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Année</label>
                        <select v-model="filtres.annee" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Toutes</option>
                            <option v-for="annee in anneeOptions" :key="annee.value" :value="annee.value">{{ annee.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jour</label>
                        <input v-model="filtres.jour" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Infraction</label>
                        <select v-model="filtres.type_infraction" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Tous</option>
                            <option v-for="type in infractionsTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between mt-3 gap-2">
                    <div class="relative flex-1 w-full sm:max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input v-model="filtres.search" type="text" placeholder="N° procédure, nom, matricule..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-slate-800 text-xs sm:text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm" @input="appliquerFiltres" />
                    </div>
                    <div class="flex gap-2">
                        <button @click="exporterListe" class="px-4 py-2 bg-emerald-500 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-emerald-600 transition-colors flex items-center gap-1">
                            <i class="pi pi-file-excel text-xs"></i> Exporter
                        </button>
                        <Link :href="route('procedures.create')" class="px-4 py-2 bg-gpj-500 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center justify-center gap-1 sm:gap-2 whitespace-nowrap">
                            <i class="pi pi-plus text-xs"></i> <span class="hidden sm:inline">Nouvelle</span> Procédure
                        </Link>
                    </div>
                </div>
            </Card>

            <!-- Compteur mobile -->
            <div class="sm:hidden text-xs text-slate-500 px-1">{{ procedures.total }} procédure(s)</div>

            <!-- Tableau Desktop -->
            <Card padding class="hidden md:block">
                <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 border-b-2 border-slate-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">N° Procédure</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Type</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Personnel(s)</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Grade</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Armée</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Lieu de commission</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Phase</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Infractions</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Date</th>
                                <th class="px-3 py-3 text-left font-semibold whitespace-nowrap text-xs uppercase tracking-wide">Parquet</th>
                                <th class="px-3 py-3 text-center font-semibold w-20 text-xs uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="procedure in procedures.data" :key="procedure.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2.5">
                                    <Link :href="route('procedures.show', procedure.id)" class="text-slate-600 font-semibold hover:underline text-xs sm:text-sm">{{ procedure.numero_procedure }}</Link>
                                    <span v-if="procedure.est_plurielle" class="ml-1 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full font-medium">Pluriel</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <Badge :variant="getTypeVariant(procedure)" size="sm" class="text-xs">
                                        {{ getTypeLabel(procedure) }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="pm.id">
                                            <div class="flex items-center gap-1 bg-slate-50 rounded-full px-2 py-0.5 border border-slate-200">
                                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 text-[10px] font-bold shrink-0">
                                                    {{ pm.militaire?.nom?.charAt(0) }}{{ pm.militaire?.prenoms?.charAt(0) }}
                                                </div>
                                                <span class="text-xs text-slate-700 truncate max-w-20 font-medium">{{ pm.militaire?.nom }} {{ pm.militaire?.prenoms }}</span>
                                                <span v-if="pm.type_personnel === 'civil'" class="text-[8px] text-slate-500 bg-slate-200 px-1 rounded font-medium">C</span>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <!-- Grade -->
                                <td class="px-3 py-2.5 text-slate-600 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="'grade-'+pm.id">
                                            <span v-if="pm.militaire?.grade?.libelle" class="text-xs bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-medium">
                                                {{ pm.militaire?.grade?.libelle }}
                                            </span>
                                            <span v-else-if="pm.type_personnel === 'civil'" class="text-xs text-slate-400">Civil</span>
                                        </template>
                                        <span v-if="!procedure.procedure_militaires?.some(p => p.militaire?.grade?.libelle || p.type_personnel === 'civil')" class="text-slate-400">-</span>
                                    </div>
                                </td>
                                <!-- Armée -->
                                <td class="px-3 py-2.5 text-slate-600 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="'armee-'+pm.id">
                                            <span v-if="pm.militaire?.armee_relation?.nom" class="text-xs bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-medium">
                                                {{ pm.militaire?.armee_relation?.nom }}
                                            </span>
                                        </template>
                                        <span v-if="!procedure.procedure_militaires?.some(p => p.militaire?.armee_relation?.nom)" class="text-slate-400">-</span>
                                    </div>
                                </td>
                                <!-- Lieu de commission -->
                                <td class="px-3 py-2.5 text-slate-600 text-xs">
                                    {{ procedure.lieu_commission || '-' }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <Badge :variant="phaseVariant(procedure.phase)" size="sm" class="text-xs">{{ procedure.phase || '-' }}</Badge>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-wrap gap-1">
                                        <!-- Infractions de la procédure -->
                                        <template v-for="inf in procedure.infractions?.slice(0, 3)" :key="'inf-'+inf.id">
                                            <Badge variant="default" size="sm" class="text-xs">{{ inf.libelle }}</Badge>
                                        </template>
                                        <!-- Infractions via procedure_militaire -->
                                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="'pminf-'+pm.id">
                                            <template v-for="infId in pm.infractions?.slice(0, 2)" :key="'pminfid-'+infId">
                                                <Badge variant="default" size="sm" class="text-xs bg-purple-50 text-purple-700">
                                                    {{ getInfractionLibelle(infId) }}
                                                </Badge>
                                            </template>
                                        </template>
                                        <!-- Plus -->
                                        <Badge v-if="getTotalInfractions(procedure) > 3" variant="neutral" size="sm" class="text-xs">
                                            +{{ getTotalInfractions(procedure) - 3 }}
                                        </Badge>
                                        <span v-if="getTotalInfractions(procedure) === 0" class="text-slate-400 text-xs">-</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-slate-600 text-xs whitespace-nowrap">{{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || '-' }}</td>
                                <td class="px-3 py-2.5 text-slate-600 text-xs">
                                    <span v-if="procedure.parquet">
                                        {{ procedure.parquet.nom }}
                                        <span class="text-[10px] text-slate-400">
                                            ({{ procedure.parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun' }})
                                        </span>
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <Link :href="route('procedures.show', procedure.id)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Voir"><i class="pi pi-eye text-xs"></i></Link>
                                        <a v-if="procedure.militaire_id && procedure.militaire?.type_personnel === 'militaire'" :href="route('militaires.casier', procedure.militaire_id)" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Casier"><i class="pi pi-print text-xs"></i></a>
                                        <button v-if="isSD" @click="confirmDeleteProcedure(procedure)" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer"><i class="pi pi-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!procedures.data?.length"><td colspan="9" class="px-4 py-12 text-center text-slate-400"><i class="pi pi-inbox text-3xl mb-2 block"></i>Aucune procédure trouvée</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="procedures.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-slate-100 gap-3">
                    <p class="text-xs sm:text-sm text-slate-500">{{ procedures.from }}-{{ procedures.to }} sur {{ procedures.total }}</p>
                    <div class="flex flex-wrap gap-1">
                        <Link v-for="link in procedures.links" :key="link.label" :href="link.url" :class="['px-2.5 py-1.5 text-xs rounded-lg transition-colors', link.active ? 'bg-gpj-500 text-white' : 'text-slate-600 hover:bg-slate-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']" v-html="link.label" />
                    </div>
                </div>
            </Card>

            <!-- Liste Mobile -->
            <div class="md:hidden space-y-2">
                <div v-if="!procedures.data?.length" class="text-center text-slate-400 py-12 bg-white rounded-xl border border-slate-200"><i class="pi pi-inbox text-3xl mb-2 block"></i>Aucune procédure</div>
                <div v-for="procedure in procedures.data" :key="procedure.id" class="bg-white rounded-xl border border-slate-200 p-3 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <Link :href="route('procedures.show', procedure.id)" class="text-slate-600 font-semibold text-sm hover:underline">{{ procedure.numero_procedure }}</Link>
                            <span v-if="procedure.est_plurielle" class="ml-1 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full font-medium">Pluriel</span>
                        </div>
                        <Badge :variant="phaseVariant(procedure.phase)" size="sm" class="text-xs">{{ procedure.phase || '-' }}</Badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="pm.id">
                            <div class="flex items-center gap-1 bg-slate-50 rounded-full px-2 py-0.5 border border-slate-200">
                                <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 text-[10px] font-bold shrink-0">
                                    {{ pm.militaire?.nom?.charAt(0) }}{{ pm.militaire?.prenoms?.charAt(0) }}
                                </div>
                                <span class="text-xs text-slate-700 truncate max-w-20 font-medium">{{ pm.militaire?.nom }} {{ pm.militaire?.prenoms }}</span>
                                <span v-if="pm.type_personnel === 'civil'" class="text-[8px] text-slate-500 bg-slate-200 px-1 rounded">C</span>
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-1 text-xs text-slate-600 mb-2">
                        <div><span class="text-slate-400 font-medium">Unité:</span> 
                            <span v-for="(pm, idx) in procedure.procedure_militaires" :key="'munite-'+pm.id">
                                {{ pm.militaire?.unite }}{{ idx < procedure.procedure_militaires.length - 1 ? ', ' : '' }}
                            </span>
                            <span v-if="!procedure.procedure_militaires?.some(p => p.militaire?.unite)" class="text-slate-400">-</span>
                        </div>
                        <div><span class="text-slate-400 font-medium">Date:</span> {{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || '-' }}</div>
                        <div><span class="text-slate-400 font-medium">Parquet:</span> {{ procedure.parquet?.nom || '-' }}</div>
                        <div><span class="text-slate-400 font-medium">Infractions:</span> 
                            <span v-if="getTotalInfractions(procedure) > 0">
                                {{ getInfractionsList(procedure).join(', ') }}
                            </span>
                            <span v-else class="text-slate-400">-</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <Link :href="route('procedures.show', procedure.id)" class="flex-1 text-center text-xs py-1.5 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-100 font-medium"><i class="pi pi-eye mr-1"></i>Voir</Link>
                        <a v-if="procedure.militaire_id && procedure.militaire?.type_personnel === 'militaire'" :href="route('militaires.casier', procedure.militaire_id)" target="_blank" class="flex-1 text-center text-xs py-1.5 bg-slate-50 text-slate-600 rounded-lg hover:bg-slate-100 font-medium"><i class="pi pi-print mr-1"></i>Casier</a>
                        <button v-if="isSD" @click="confirmDeleteProcedure(procedure)" class="px-3 py-1.5 text-xs text-red-500 bg-red-50 rounded-lg hover:bg-red-100"><i class="pi pi-trash"></i></button>
                    </div>
                </div>
            </div>

            <!-- Pagination mobile -->
            <div v-if="procedures.links?.length > 3" class="md:hidden flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <Link v-for="link in procedures.links" :key="link.label" :href="link.url" :class="['px-2.5 py-1.5 text-xs rounded-lg', link.active ? 'bg-gpj-500 text-white' : 'text-slate-600 hover:bg-slate-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']" v-html="link.label" />
                </div>
            </div>

            <!-- Modal confirmation suppression -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white dark:bg-slate-900 rounded-xl p-5 sm:p-6 max-w-md w-full shadow-xl">
                    <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0"><i class="pi pi-exclamation-triangle text-red-600"></i></div><h3 class="text-lg font-bold text-slate-800">Confirmer la suppression</h3></div>
                    <p class="text-sm text-slate-600 mb-2">Vous êtes sur le point de supprimer :</p>
                    <p class="text-sm font-bold text-slate-800 mb-4">{{ procedureToDelete?.numero_procedure }}</p>
                    <p class="text-sm text-red-500 mb-6">⚠️ Action irréversible.</p>
                    <div class="flex gap-3 justify-end"><button @click="showDeleteModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 cursor-pointer">Annuler</button><button @click="deleteProcedure" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer"><i v-if="deleteProcessing" class="pi pi-spin pi-spinner mr-1"></i>Supprimer</button></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref, computed, watch, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const page = usePage();
const props = defineProps({ 
    procedures: Object, 
    filters: Object, 
    infractionsTypes: Array,
    moisOptions: Array,
    anneeOptions: Array,
    typePersonnelOptions: Array,
});

const isSD = computed(() => page.props.auth?.user?.role === 'ADMIN');

// ====== INFOS INFRACTIONS ======
const infractionsData = ref({});

// Charger les infractions pour les libellés
const loadInfractions = async () => {
    try {
        const response = await fetch('/infractions-data');
        const data = await response.json();
        // Transformer en objet pour accès rapide par ID
        const map = {};
        data.forEach(inf => {
            map[inf.id] = inf.libelle;
        });
        infractionsData.value = map;
        console.log('✅ Infractions chargées:', Object.keys(map).length);
    } catch (e) {
        console.error('Erreur chargement infractions:', e);
    }
};
loadInfractions();

const getInfractionLibelle = (id) => {
    if (!id) return 'Non définie';
    const libelle = infractionsData.value[id];
    return libelle || `Infraction #${id}`;
};

const getTotalInfractions = (procedure) => {
    let count = procedure.infractions?.length || 0;
    procedure.procedure_militaires?.forEach(pm => {
        count += pm.infractions?.length || 0;
    });
    return count;
};

const getAllInfractionsIds = (procedure) => {
    const ids = [];
    procedure.infractions?.forEach(inf => {
        ids.push(inf.id);
    });
    procedure.procedure_militaires?.forEach(pm => {
        pm.infractions?.forEach(id => {
            if (!ids.includes(id)) {
                ids.push(id);
            }
        });
    });
    return ids;
};

const getInfractionsList = (procedure) => {
    const ids = getAllInfractionsIds(procedure);
    return ids.map(id => getInfractionLibelle(id)).slice(0, 5);
};

const flashMessage = ref(null);
watch(() => page.props.flash?.success, m => { if (m) { flashMessage.value = { type: 'success', message: m }; setTimeout(() => flashMessage.value = null, 4000); }}, { immediate: true });
watch(() => page.props.flash?.error, m => { if (m) { flashMessage.value = { type: 'error', message: m }; setTimeout(() => flashMessage.value = null, 5000); }}, { immediate: true });
onMounted(() => { 
    if (page.props.flash?.success) { 
        flashMessage.value = { type: 'success', message: page.props.flash.success }; 
        setTimeout(() => flashMessage.value = null, 4000); 
    } 
    if (page.props.flash?.error) { 
        flashMessage.value = { type: 'error', message: page.props.flash.error }; 
        setTimeout(() => flashMessage.value = null, 5000); 
    }
});

const filtres = reactive({
    phase: props.filters?.phase || '',
    type_personnel: props.filters?.type_personnel || '',
    type_infraction: props.filters?.type_infraction || '',
    mois: props.filters?.mois || '',
    annee: props.filters?.annee || '',
    jour: props.filters?.jour || '',
    search: props.filters?.search || ''
});

let timeout = null;
const appliquerFiltres = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('procedures.index'), filtres, { preserveState: true, replace: true });
    }, 300);
};

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

const phaseVariant = (phase) => {
    const map = { 
        'Ordre de Poursuite': 'warning', 
        'Mise à Disposition': 'danger', 
        'Communiqué': 'info', 
        'Brouillon': 'neutral' 
    };
    return map[phase] || 'default';
};

const getTypeLabel = (procedure) => {
    if (!procedure.procedure_militaires || procedure.procedure_militaires.length === 0) return 'N/A';
    const types = procedure.procedure_militaires.map(pm => pm.type_personnel);
    if (types.every(t => t === 'militaire')) return 'Militaire';
    if (types.every(t => t === 'civil')) return 'Civil';
    return 'Mixte';
};

const getTypeVariant = (procedure) => {
    const label = getTypeLabel(procedure);
    const map = {
        'Militaire': 'info',
        'Civil': 'primary',
        'Mixte': 'warning'
    };
    return map[label] || 'default';
};

const exporterListe = () => {
    const params = new URLSearchParams();
    Object.keys(filtres).forEach(key => {
        if (filtres[key]) {
            params.append(key, filtres[key]);
        }
    });
    window.open(`/exports/procedures-liste?${params.toString()}`, '_blank');
};

const showDeleteModal = ref(false);
const deleteProcessing = ref(false);
const procedureToDelete = ref(null);

const confirmDeleteProcedure = (p) => {
    procedureToDelete.value = p;
    showDeleteModal.value = true;
};

const deleteProcedure = () => {
    if (!procedureToDelete.value) return;
    deleteProcessing.value = true;
    router.delete(route('procedures.destroy', procedureToDelete.value.id), {
        onSuccess: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
            procedureToDelete.value = null;
        },
        onError: () => {
            deleteProcessing.value = false;
        },
        preserveScroll: true,
    });
};
</script>
<script>export default { layout: null };</script>