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
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Phase</label>
                        <select v-model="filtres.phase" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Toutes</option>
                            <option value="Ordre de Poursuite">Ordre de Poursuite</option>
                            <option value="Mise à Disposition">Mise à Disposition</option>
                            <option value="Communiqué">Communiqué</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Infraction</label>
                        <select v-model="filtres.type_infraction" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Tous</option>
                            <option v-for="type in infractionsTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Du</label>
                        <input v-model="filtres.date_debut" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Au</label>
                        <input v-model="filtres.date_fin" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-xs sm:text-sm py-2 px-2 sm:px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between mt-3 gap-2">
                    <div class="relative flex-1 w-full sm:max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400 text-sm"></i>
                        <input v-model="filtres.search" type="text" placeholder="N° procédure, nom, matricule..." class="w-full pl-9 pr-3 py-2 rounded-lg border border-gpj-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" @input="appliquerFiltres" />
                    </div>
                    <Link :href="route('procedures.create')" class="px-4 py-2 bg-gpj-500 text-white text-xs sm:text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center justify-center gap-1 sm:gap-2 whitespace-nowrap">
                        <i class="pi pi-plus text-xs"></i> <span class="hidden sm:inline">Nouvelle</span> Procédure
                    </Link>
                </div>
            </Card>

            <!-- Compteur mobile -->
            <div class="sm:hidden text-xs text-gpj-400 px-1">{{ procedures.total }} procédure(s)</div>

            <!-- Tableau Desktop -->
            <Card padding class="hidden md:block">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gpj-50 text-gpj-600">
                            <tr>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap">N° Procédure</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap">Militaires</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap hidden lg:table-cell">Unité</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap">Phase</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap hidden xl:table-cell">Infractions</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap">Date</th>
                                <th class="px-3 py-2.5 text-left font-semibold whitespace-nowrap hidden lg:table-cell">Parquet</th>
                                <th class="px-3 py-2.5 text-center font-semibold w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="procedure in procedures.data" :key="procedure.id" class="hover:bg-gpj-50 transition-colors">
                                <td class="px-3 py-2.5">
                                    <Link :href="route('procedures.show', procedure.id)" class="text-gpj-500 font-medium hover:underline text-xs sm:text-sm">{{ procedure.numero_procedure }}</Link>
                                    <span v-if="procedure.est_plurielle" class="ml-1 text-[10px] bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded-full">Pluriel</span>
                                </td>
                                <td class="px-3 py-2.5">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <!-- Militaire principal -->
                                        <div v-if="procedure.militaire" class="flex items-center gap-1">
                                            <div class="w-6 h-6 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-600 text-[10px] font-bold shrink-0">
                                                {{ procedure.militaire?.nom?.charAt(0) }}{{ procedure.militaire?.prenoms?.charAt(0) }}
                                            </div>
                                            <span class="text-xs text-gpj-700 truncate max-w-[80px]">{{ procedure.militaire?.nom }} {{ procedure.militaire?.prenoms }}</span>
                                        </div>
                                        <!-- Autres militaires (si pluriel) -->
                                        <div v-if="procedure.est_plurielle && procedure.procedure_militaires?.length > 1" class="flex items-center gap-0.5">
                                            <template v-for="(pm, idx) in procedure.procedure_militaires" :key="pm.id">
                                                <div v-if="pm.militaire_id !== procedure.militaire_id" class="flex items-center gap-0.5">
                                                    <span class="text-xs text-gpj-400">+</span>
                                                    <div class="w-5 h-5 rounded-full bg-gpj-50 border border-gpj-200 flex items-center justify-center text-gpj-500 text-[8px] font-bold shrink-0">
                                                        {{ pm.militaire?.nom?.charAt(0) }}{{ pm.militaire?.prenoms?.charAt(0) }}
                                                    </div>
                                                    <span class="text-[10px] text-gpj-400 truncate max-w-[50px]">{{ pm.militaire?.nom }}</span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-gpj-600 text-xs hidden lg:table-cell">
                                    {{ procedure.militaire?.unite || '-' }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <Badge :variant="phaseVariant(procedure.phase)" size="sm" class="text-xs">{{ procedure.phase || '-' }}</Badge>
                                </td>
                                <td class="px-3 py-2.5 hidden xl:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="inf in procedure.infractions?.slice(0, 2)" :key="inf.id" variant="default" size="sm" class="text-xs">{{ inf.libelle }}</Badge>
                                        <Badge v-if="procedure.infractions?.length > 2" variant="neutral" size="sm" class="text-xs">+{{ procedure.infractions.length - 2 }}</Badge>
                                    </div>
                                </td>
                                <td class="px-3 py-2.5 text-gpj-600 text-xs whitespace-nowrap">{{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || '-' }}</td>
                                <td class="px-3 py-2.5 text-gpj-600 text-xs hidden lg:table-cell">{{ procedure.parquet_competent || '-' }}</td>
                                <td class="px-3 py-2.5 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <Link :href="route('procedures.show', procedure.id)" class="w-7 h-7 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Voir"><i class="pi pi-eye text-xs"></i></Link>
                                        <a v-if="procedure.militaire_id" :href="route('militaires.casier', procedure.militaire_id)" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Casier"><i class="pi pi-print text-xs"></i></a>
                                        <button v-if="isSD" @click="confirmDeleteProcedure(procedure)" class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer"><i class="pi pi-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!procedures.data?.length"><td colspan="8" class="px-4 py-12 text-center text-gpj-400"><i class="pi pi-inbox text-3xl mb-2 block"></i>Aucune procédure trouvée</td></tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="procedures.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-gpj-100 gap-3">
                    <p class="text-xs sm:text-sm text-gpj-400">{{ procedures.from }}-{{ procedures.to }} sur {{ procedures.total }}</p>
                    <div class="flex flex-wrap gap-1">
                        <Link v-for="link in procedures.links" :key="link.label" :href="link.url" :class="['px-2.5 py-1.5 text-xs rounded-lg transition-colors', link.active ? 'bg-gpj-500 text-white' : 'text-gpj-600 hover:bg-gpj-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']" v-html="link.label" />
                    </div>
                </div>
            </Card>

            <!-- Liste Mobile -->
            <div class="md:hidden space-y-2">
                <div v-if="!procedures.data?.length" class="text-center text-gpj-400 py-12 bg-white rounded-xl border border-gpj-200"><i class="pi pi-inbox text-3xl mb-2 block"></i>Aucune procédure</div>
                <div v-for="procedure in procedures.data" :key="procedure.id" class="bg-white rounded-xl border border-gpj-200 p-3 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <Link :href="route('procedures.show', procedure.id)" class="text-gpj-500 font-semibold text-sm hover:underline">{{ procedure.numero_procedure }}</Link>
                            <span v-if="procedure.est_plurielle" class="ml-1 text-[10px] bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded-full">Pluriel</span>
                        </div>
                        <Badge :variant="phaseVariant(procedure.phase)" size="sm" class="text-xs">{{ procedure.phase || '-' }}</Badge>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <template v-for="(pm, idx) in procedure.procedure_militaires" :key="pm.id">
                            <div class="flex items-center gap-1 bg-gpj-50 rounded-full px-2 py-0.5">
                                <div class="w-6 h-6 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-600 text-[10px] font-bold shrink-0">
                                    {{ pm.militaire?.nom?.charAt(0) }}{{ pm.militaire?.prenoms?.charAt(0) }}
                                </div>
                                <span class="text-xs text-gpj-700 truncate max-w-[80px]">{{ pm.militaire?.nom }} {{ pm.militaire?.prenoms }}</span>
                            </div>
                        </template>
                    </div>
                    <div class="grid grid-cols-2 gap-1 text-xs text-gpj-500 mb-2">
                        <div><span class="text-gpj-400">Unité:</span> {{ procedure.militaire?.unite || '-' }}</div>
                        <div><span class="text-gpj-400">Date:</span> {{ formatDate(procedure.date_ouverture) || formatDate(procedure.created_at) || '-' }}</div>
                        <div><span class="text-gpj-400">Parquet:</span> {{ procedure.parquet_competent || '-' }}</div>
                        <div v-if="procedure.infractions?.length"><span class="text-gpj-400">Infractions:</span> {{ procedure.infractions.map(i => i.libelle).join(', ') }}</div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-gpj-100">
                        <Link :href="route('procedures.show', procedure.id)" class="flex-1 text-center text-xs py-1.5 bg-gpj-50 text-gpj-600 rounded-lg hover:bg-gpj-100"><i class="pi pi-eye mr-1"></i>Voir</Link>
                        <a v-if="procedure.militaire_id" :href="route('militaires.casier', procedure.militaire_id)" target="_blank" class="flex-1 text-center text-xs py-1.5 bg-gpj-50 text-gpj-600 rounded-lg hover:bg-gpj-100"><i class="pi pi-print mr-1"></i>Casier</a>
                        <button v-if="isSD" @click="confirmDeleteProcedure(procedure)" class="px-3 py-1.5 text-xs text-red-500 bg-red-50 rounded-lg hover:bg-red-100"><i class="pi pi-trash"></i></button>
                    </div>
                </div>
            </div>

            <!-- Pagination mobile -->
            <div v-if="procedures.links?.length > 3" class="md:hidden flex justify-center">
                <div class="flex flex-wrap gap-1">
                    <Link v-for="link in procedures.links" :key="link.label" :href="link.url" :class="['px-2.5 py-1.5 text-xs rounded-lg', link.active ? 'bg-gpj-500 text-white' : 'text-gpj-600 hover:bg-gpj-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']" v-html="link.label" />
                </div>
            </div>

            <!-- Modal confirmation suppression -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white dark:bg-gpj-900 rounded-xl p-5 sm:p-6 max-w-md w-full shadow-xl">
                    <div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0"><i class="pi pi-exclamation-triangle text-red-600"></i></div><h3 class="text-lg font-bold text-gpj-800">Confirmer la suppression</h3></div>
                    <p class="text-sm text-gpj-600 mb-2">Vous êtes sur le point de supprimer :</p>
                    <p class="text-sm font-bold text-gpj-800 mb-4">{{ procedureToDelete?.numero_procedure }}</p>
                    <p class="text-sm text-red-500 mb-6">⚠️ Action irréversible.</p>
                    <div class="flex gap-3 justify-end"><button @click="showDeleteModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button><button @click="deleteProcedure" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer"><i v-if="deleteProcessing" class="pi pi-spin pi-spinner mr-1"></i>Supprimer</button></div>
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
const props = defineProps({ procedures: Object, filters: Object, infractionsTypes: Array });
const isSD = computed(() => page.props.auth.user.role === 'SD');

const flashMessage = ref(null);
watch(() => page.props.flash?.success, m => { if (m) { flashMessage.value = { type: 'success', message: m }; setTimeout(() => flashMessage.value = null, 4000); }}, { immediate: true });
watch(() => page.props.flash?.error, m => { if (m) { flashMessage.value = { type: 'error', message: m }; setTimeout(() => flashMessage.value = null, 5000); }}, { immediate: true });
onMounted(() => { if (page.props.flash?.success) { flashMessage.value = { type: 'success', message: page.props.flash.success }; setTimeout(() => flashMessage.value = null, 4000); } if (page.props.flash?.error) { flashMessage.value = { type: 'error', message: page.props.flash.error }; setTimeout(() => flashMessage.value = null, 5000); }});

const filtres = reactive({ phase: props.filters?.phase || '', type_infraction: props.filters?.type_infraction || '', date_debut: props.filters?.date_debut || '', date_fin: props.filters?.date_fin || '', search: props.filters?.search || '' });
let timeout = null;
const appliquerFiltres = () => { clearTimeout(timeout); timeout = setTimeout(() => { router.get(route('procedures.index'), filtres, { preserveState: true, replace: true }); }, 300); };
const formatDate = d => d ? new Date(d).toLocaleDateString('fr-FR') : null;
const phaseVariant = phase => ({ 'Ordre de Poursuite': 'warning', 'Mise à Disposition': 'danger', 'Communiqué': 'info', Brouillon: 'neutral' }[phase] || 'default');

const showDeleteModal = ref(false); const deleteProcessing = ref(false); const procedureToDelete = ref(null);
const confirmDeleteProcedure = (p) => { procedureToDelete.value = p; showDeleteModal.value = true; };
const deleteProcedure = () => { if (!procedureToDelete.value) return; deleteProcessing.value = true; router.delete(route('procedures.destroy', procedureToDelete.value.id), { onSuccess: () => { deleteProcessing.value = false; showDeleteModal.value = false; procedureToDelete.value = null; }, onError: () => { deleteProcessing.value = false; }, preserveScroll: true }); };
</script>
<script>export default { layout: null };</script>