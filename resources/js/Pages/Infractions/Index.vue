<template>
    <AuthenticatedLayout title="Infractions" subtitle="Nomenclature des infractions">
        <div class="space-y-4">
            <div class="flex justify-end">
                <Link
                    :href="route('infractions.create')"
                    class="px-4 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-plus"></i> Nouvelle Infraction
                </Link>
            </div>

            <Card padding>
                <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-260px)]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 border-b-2 border-slate-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Code</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Libellé</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Classification</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Nature</th>
                                <th class="px-2 py-1.5 text-center font-semibold text-xs uppercase tracking-wide">Gravité</th>
                                <th class="px-2 py-1.5 text-center font-semibold text-xs uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="infraction in infractions.data" :key="infraction.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-2 py-1.5">
                                    <span class="font-mono text-xs px-2 py-0.5 rounded" :class="codeBadgeClass(infraction.code_infraction)">
                                        {{ infraction.code_infraction }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 font-medium text-slate-800">{{ infraction.libelle }}</td>
                                <td class="px-2 py-1.5">
                                    <Badge :variant="classificationVariant(infraction.classification)" size="sm">
                                        {{ infraction.classification }}
                                    </Badge>
                                </td>
                                <td class="px-2 py-1.5 text-slate-600 text-xs">{{ infraction.nature }}</td>
                                <td class="px-2 py-1.5 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <i v-for="i in 5" :key="i" class="pi text-xs"
                                            :class="i <= infraction.gravite ? 'pi-star-fill text-amber-500' : 'pi-star text-slate-200'"></i>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('infractions.edit', infraction.id)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Modifier">
                                            <i class="pi pi-pencil text-sm"></i>
                                        </Link>
                                        <button v-if="isSD" @click="confirmDeleteInfraction(infraction)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600" title="Supprimer">
                                            <i class="pi pi-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!infractions.data?.length">
                                <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucune infraction
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="infractions.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-slate-100 gap-3">
                    <p class="text-sm text-slate-500">
                        {{ infractions.from }}-{{ infractions.to }} sur {{ infractions.total }}
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link v-for="link in infractions.links" :key="link.label" :href="link.url"
                            :class="['px-3 py-1.5 text-sm rounded-lg transition-colors', link.active ? 'bg-gpj-500 text-white' : 'text-slate-600 hover:bg-slate-100', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </Card>

            <!-- Modal confirmation suppression -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white dark:bg-slate-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <i class="pi pi-exclamation-triangle text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">Confirmer la suppression</h3>
                    </div>
                    <p class="text-sm text-slate-600 mb-2">
                        Vous êtes sur le point de supprimer définitivement l'infraction :
                    </p>
                    <p class="text-sm font-bold text-slate-800 mb-2">
                        {{ infractionToDelete?.code_infraction }} - {{ infractionToDelete?.libelle }}
                    </p>
                    <p class="text-sm text-red-500 mb-6">
                        ⚠️ Cette action est irréversible. Les infractions utilisées dans des procédures ne peuvent pas être supprimées.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 cursor-pointer">
                            Annuler
                        </button>
                        <button @click="deleteInfraction" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer">
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
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const page = usePage();

defineProps({
    infractions: Object,
});

// Vérifier si l'utilisateur est Admin
const isSD = computed(() => page.props.auth.user.role === 'ADMIN');

const classificationVariant = (c) => {
    const m = { 'Criminelle': 'danger', 'Délictuelle': 'warning', 'Contravention': 'info' };
    return m[c] || 'default';
};

const codeBadgeClass = (code) => {
    if (!code) return 'bg-slate-100 text-slate-600';
    if (code.startsWith('INF-CR')) return 'bg-red-100 text-red-700';
    if (code.startsWith('INF-DE')) return 'bg-amber-100 text-amber-700';
    if (code.startsWith('INF-CO')) return 'bg-sky-100 text-sky-700';
    return 'bg-slate-100 text-slate-600';
};

// ==================== SUPPRESSION ====================
const showDeleteModal = ref(false);
const deleteProcessing = ref(false);
const infractionToDelete = ref(null);

const confirmDeleteInfraction = (infraction) => {
    infractionToDelete.value = infraction;
    showDeleteModal.value = true;
};

const deleteInfraction = () => {
    if (!infractionToDelete.value) return;
    deleteProcessing.value = true;
    router.delete(route('infractions.destroy', infractionToDelete.value.id), {
        onSuccess: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
            infractionToDelete.value = null;
        },
        onError: () => {
            deleteProcessing.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<script>export default { layout: null };</script>