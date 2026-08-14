<template>
    <AuthenticatedLayout title="Personnels" subtitle="Recherche et consultation des dossiers">
        <div class="space-y-4">
            <!-- Filtres -->
            <Card>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-50 max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input
                            v-model="filtres.search"
                            type="text"
                            placeholder="Rechercher par nom, prénom, matricule..."
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm transition-colors"
                            @input="appliquerFiltres"
                        />
                    </div>
                    <select
                        v-model="filtres.type_personnel"
                        class="rounded-lg border border-slate-300 text-sm text-slate-800 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm transition-colors"
                        @change="appliquerFiltres"
                    >
                        <option value="">Tous les types</option>
                        <option value="militaire">Militaire</option>
                        <option value="civil">Civil</option>
                    </select>
                    <select
                        v-model="filtres.statut"
                        class="rounded-lg border border-slate-300 text-sm text-slate-800 py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm transition-colors"
                        @change="appliquerFiltres"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="En activité">En activité</option>
                        <option value="Non activite">Non activite</option>
                        <option value="En retraite">En retraite</option>
                        <option value="Radié">Radié</option>
                    </select>
                    <div class="flex-1"></div>
                    <Link
                        :href="route('militaires.create')"
                        class="px-4 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-2 whitespace-nowrap"
                    >
                        <i class="pi pi-plus"></i> Nouveau Personnel
                    </Link>
                </div>
            </Card>

            <!-- Tableau -->
            <Card padding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 border-b-2 border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Type</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Matricule</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Identité</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Grade / Profession</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Armée/Service</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Unité</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Genre</th>
                                <th class="px-4 py-3 text-left font-semibold text-xs uppercase tracking-wide">Statut</th>
                                <th class="px-4 py-3 text-center font-semibold text-xs uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="personnel in militaires.data" :key="personnel.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <Badge :variant="personnel.type_personnel === 'militaire' ? 'info' : 'primary'" size="sm">
                                        {{ personnel.type_personnel === 'militaire' ? 'Militaire' : 'Civil' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Link :href="route('militaires.show', personnel.id)" class="text-gpj-500 font-medium hover:underline">
                                        {{ personnel.matricule || 'N/A' }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-700 text-xs font-bold shrink-0">
                                            {{ personnel.nom ? personnel.nom.charAt(0) : '?' }}{{ personnel.prenoms ? personnel.prenoms.charAt(0) : '' }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-slate-800">{{ personnel.nom || 'Nom inconnu' }} {{ personnel.prenoms || '' }}</p>
                                            <p class="text-xs text-slate-400">{{ personnel.matricule || 'Sans matricule' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ personnel.type_personnel === 'militaire' ? (personnel.grade?.libelle || personnel.grade || '-') : (personnel.profession || '-') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    {{ personnel.armee_relation?.nom || personnel.armee || '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 text-xs">{{ personnel.unite || '-' }}</td>
                                <td class="px-4 py-3 text-slate-600 text-xs">
                                    <span v-if="personnel.genre">{{ personnel.genre }}</span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="statutVariant(personnel.statut)" size="sm">
                                        {{ personnel.statut || 'Non défini' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('militaires.show', personnel.id)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Voir la fiche">
                                            <i class="pi pi-eye text-sm"></i>
                                        </Link>
                                        <Link :href="route('militaires.edit', personnel.id)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Modifier">
                                            <i class="pi pi-pencil text-sm"></i>
                                        </Link>
                                        <a v-if="personnel.type_personnel === 'militaire'" :href="route('militaires.casier', personnel.id)" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Casier judiciaire">
                                            <i class="pi pi-print text-sm"></i>
                                        </a>
                                        <button v-if="isSD" @click="confirmDeletePersonnel(personnel)" class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer">
                                            <i class="pi pi-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!militaires.data?.length">
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucun personnel trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="militaires?.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-slate-100 gap-3">
                    <p class="text-sm text-slate-500">
                        {{ militaires.from ?? 0 }}-{{ militaires.to ?? 0 }} sur {{ militaires.total ?? 0 }} résultats
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, index) in militaires.links" 
                            :key="index" 
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                link.active ? 'bg-gpj-500 text-white' : 'text-slate-600 hover:bg-slate-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label || '...'"
                            @click.prevent="!link.url ? $event.preventDefault() : null"
                        />
                    </div>
                </div>
            </Card>

            <!-- Modal confirmation suppression -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <i class="pi pi-exclamation-triangle text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Confirmer la suppression</h3>
                    </div>
                    <p class="text-sm text-slate-600 mb-2">
                        Vous êtes sur le point de supprimer définitivement le personnel :
                    </p>
                    <p class="text-sm font-bold text-slate-800 mb-2">
                        {{ personnelToDelete?.matricule || 'Sans matricule' }} - {{ personnelToDelete?.nom || 'Nom inconnu' }} {{ personnelToDelete?.prenoms || '' }}
                    </p>
                    <p class="text-sm text-red-500 mb-6">
                        ⚠️ Cette action est irréversible. Les personnels ayant des procédures judiciaires ne peuvent pas être supprimés.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 cursor-pointer">
                            Annuler
                        </button>
                        <button @click="deletePersonnel" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer">
                            <i v-if="deleteProcessing" class="pi pi-spin pi-spinner mr-1"></i>
                            Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const page = usePage();
const props = defineProps({
    militaires: {
        type: Object,
        default: () => ({ 
            data: [], 
            links: [], 
            from: 0, 
            to: 0, 
            total: 0 
        })
    },
    filters: {
        type: Object,
        default: () => ({ search: '', statut: '', type_personnel: '' })
    },
});

const isSD = computed(() => page.props.auth?.user?.role === 'ADMIN');

const filtres = reactive({
    search: props.filters?.search || '',
    statut: props.filters?.statut || '',
    type_personnel: props.filters?.type_personnel || '',
});

let timeout = null;
const appliquerFiltres = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('militaires.index'), filtres, { 
            preserveState: true, 
            replace: true 
        });
    }, 300);
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

const showDeleteModal = ref(false);
const deleteProcessing = ref(false);
const personnelToDelete = ref(null);

const confirmDeletePersonnel = (personnel) => {
    personnelToDelete.value = personnel;
    showDeleteModal.value = true;
};

const deletePersonnel = () => {
    if (!personnelToDelete.value) return;
    deleteProcessing.value = true;
    router.delete(route('militaires.destroy', personnelToDelete.value.id), {
        onSuccess: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
            personnelToDelete.value = null;
        },
        onError: () => {
            deleteProcessing.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<script>export default { layout: null };</script>