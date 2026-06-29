<template>
    <AuthenticatedLayout title="Militaires" subtitle="Recherche et consultation des dossiers">
        <div class="space-y-4">
            <!-- Filtres -->
            <Card>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-50 max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400 text-sm"></i>
                        <input
                            v-model="filtres.search"
                            type="text"
                            placeholder="Rechercher par nom, prénom, matricule..."
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 transition-colors"
                            @input="appliquerFiltres"
                        />
                    </div>
                    <select
                        v-model="filtres.statut"
                        class="rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500 transition-colors"
                        @change="appliquerFiltres"
                    >
                        <option value="">Tous les statuts</option>
                        <option value="Actif">Actif</option>
                        <option value="Suspendu">Suspendu</option>
                        <option value="Déserteur">Déserteur</option>
                        <option value="Radié">Radié</option>
                    </select>
                    <div class="flex-1"></div>
                    <Link
                        :href="route('militaires.create')"
                        class="px-4 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-2 whitespace-nowrap"
                    >
                        <i class="pi pi-plus"></i> Nouveau Militaire
                    </Link>
                </div>
            </Card>

            <!-- Tableau -->
            <Card padding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gpj-50 text-gpj-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Matricule</th>
                                <th class="px-4 py-3 text-left font-semibold">Identité</th>
                                <th class="px-4 py-3 text-left font-semibold">Grade</th>
                                <th class="px-4 py-3 text-left font-semibold">Armée/Service</th>
                                <th class="px-4 py-3 text-left font-semibold">Unité</th>
                                <th class="px-4 py-3 text-left font-semibold">Genre</th>
                                <th class="px-4 py-3 text-left font-semibold">Statut</th>
                                <th class="px-4 py-3 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="militaire in militaires.data" :key="militaire.id" class="hover:bg-gpj-50 transition-colors">
                                <td class="px-4 py-3">
                                    <Link :href="route('militaires.show', militaire.id)" class="text-gpj-500 font-medium hover:underline">
                                        {{ militaire.matricule || 'Non défini' }}
                                    </Link>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-600 text-xs font-bold shrink-0">
                                            {{ militaire.nom ? militaire.nom.charAt(0) : '?' }}{{ militaire.prenoms ? militaire.prenoms.charAt(0) : '' }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gpj-800">{{ militaire.nom || 'Nom inconnu' }} {{ militaire.prenoms || '' }}</p>
                                            <p class="text-xs text-gpj-400">{{ militaire.matricule || 'Sans matricule' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gpj-600">{{ militaire.grade?.libelle || militaire.grade || '-' }}</td>
                                <td class="px-4 py-3 text-gpj-600 text-xs">{{ militaire.armee || '-' }}</td>
                                <td class="px-4 py-3 text-gpj-600 text-xs">{{ militaire.unite || '-' }}</td>
                                <td class="px-4 py-3 text-gpj-600 text-xs">
                                    <span v-if="militaire.genre">{{ militaire.genre }}</span>
                                    <span v-else class="text-gpj-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="statutVariant(militaire.statut)" size="sm">
                                        {{ militaire.statut || 'Non défini' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('militaires.show', militaire.id)" class="w-8 h-8 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Voir la fiche">
                                            <i class="pi pi-eye text-sm"></i>
                                        </Link>
                                        <Link :href="route('militaires.edit', militaire.id)" class="w-8 h-8 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Modifier">
                                            <i class="pi pi-pencil text-sm"></i>
                                        </Link>
                                        <a :href="route('militaires.casier', militaire.id)" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Casier judiciaire">
                                            <i class="pi pi-print text-sm"></i>
                                        </a>
                                        <button v-if="isSD" @click="confirmDeleteMilitaire(militaire)" class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer">
                                            <i class="pi pi-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!militaires.data?.length">
                                <td colspan="8" class="px-4 py-12 text-center text-gpj-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucun militaire trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="militaires.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-gpj-100 gap-3">
                    <p class="text-sm text-gpj-400">
                        {{ militaires.from }}-{{ militaires.to }} sur {{ militaires.total }} résultats
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in militaires.links" :key="link.label" :href="link.url"
                            :class="['px-3 py-1.5 text-sm rounded-lg transition-colors', link.active ? 'bg-gpj-500 text-white' : 'text-gpj-600 hover:bg-gpj-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                            v-html="link.label"
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
                        <h3 class="text-lg font-bold text-gpj-800">Confirmer la suppression</h3>
                    </div>
                    <p class="text-sm text-gpj-600 mb-2">
                        Vous êtes sur le point de supprimer définitivement le militaire :
                    </p>
                    <p class="text-sm font-bold text-gpj-800 mb-2">
                        {{ militaireToDelete?.matricule || 'Sans matricule' }} - {{ militaireToDelete?.nom || 'Nom inconnu' }} {{ militaireToDelete?.prenoms || '' }}
                    </p>
                    <p class="text-sm text-red-500 mb-6">
                        ⚠️ Cette action est irréversible. Les militaires ayant des procédures judiciaires ne peuvent pas être supprimés.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                            Annuler
                        </button>
                        <button @click="deleteMilitaire" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer">
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
    militaires: Object,
    filters: Object,
});

const isSD = computed(() => page.props.auth.user.role === 'SD');

const filtres = reactive({
    search: props.filters?.search || '',
    statut: props.filters?.statut || '',
});

let timeout = null;
const appliquerFiltres = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('militaires.index'), filtres, { preserveState: true, replace: true });
    }, 300);
};

const statutVariant = (s) => {
    const m = { Actif: 'success', Suspendu: 'warning', Déserteur: 'danger', Radié: 'neutral' };
    return m[s] || 'default';
};

const showDeleteModal = ref(false);
const deleteProcessing = ref(false);
const militaireToDelete = ref(null);

const confirmDeleteMilitaire = (militaire) => {
    militaireToDelete.value = militaire;
    showDeleteModal.value = true;
};

const deleteMilitaire = () => {
    if (!militaireToDelete.value) return;
    deleteProcessing.value = true;
    router.delete(route('militaires.destroy', militaireToDelete.value.id), {
        onSuccess: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
            militaireToDelete.value = null;
        },
        onError: () => {
            deleteProcessing.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<script>export default { layout: null };</script>