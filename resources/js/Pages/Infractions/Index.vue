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
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gpj-50 text-gpj-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Code</th>
                                <th class="px-4 py-3 text-left font-semibold">Libellé</th>
                                <th class="px-4 py-3 text-left font-semibold">Classification</th>
                                <th class="px-4 py-3 text-left font-semibold">Nature</th>
                                <th class="px-4 py-3 text-center font-semibold">Gravité</th>
                                <th class="px-4 py-3 text-center font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="infraction in infractions.data" :key="infraction.id" class="hover:bg-gpj-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs px-2 py-0.5 rounded" :class="codeBadgeClass(infraction.code_infraction)">
                                        {{ infraction.code_infraction }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gpj-800">{{ infraction.libelle }}</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="classificationVariant(infraction.classification)" size="sm">
                                        {{ infraction.classification }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-gpj-600 text-xs">{{ infraction.nature }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-0.5">
                                        <i v-for="i in 5" :key="i" class="pi text-xs"
                                            :class="i <= infraction.gravite ? 'pi-star-fill text-amber-500' : 'pi-star text-gpj-200'"></i>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <Link :href="route('infractions.edit', infraction.id)"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gpj-400 hover:bg-gpj-100 hover:text-gpj-600" title="Modifier">
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
                                <td colspan="6" class="px-4 py-12 text-center text-gpj-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucune infraction
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="infractions.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-gpj-100 gap-3">
                    <p class="text-sm text-gpj-400">
                        {{ infractions.from }}-{{ infractions.to }} sur {{ infractions.total }}
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link v-for="link in infractions.links" :key="link.label" :href="link.url"
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
                        Vous êtes sur le point de supprimer définitivement l'infraction :
                    </p>
                    <p class="text-sm font-bold text-gpj-800 mb-2">
                        {{ infractionToDelete?.code_infraction }} - {{ infractionToDelete?.libelle }}
                    </p>
                    <p class="text-sm text-red-500 mb-6">
                        ⚠️ Cette action est irréversible. Les infractions utilisées dans des procédures ne peuvent pas être supprimées.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
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

// Vérifier si l'utilisateur est SD
const isSD = computed(() => page.props.auth.user.role === 'SD');

const classificationVariant = (c) => {
    const m = { 'Criminelle': 'danger', 'Délictuelle': 'warning', 'Contravention': 'info' };
    return m[c] || 'default';
};

const codeBadgeClass = (code) => {
    if (!code) return 'bg-gpj-100 text-gpj-600';
    if (code.startsWith('INF-CR')) return 'bg-red-100 text-red-700';
    if (code.startsWith('INF-DE')) return 'bg-amber-100 text-amber-700';
    if (code.startsWith('INF-CO')) return 'bg-sky-100 text-sky-700';
    return 'bg-gpj-100 text-gpj-600';
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