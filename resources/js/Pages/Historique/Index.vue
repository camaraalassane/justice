<template>
    <AuthenticatedLayout title="Historique" subtitle="Journal des activités">
        <div class="space-y-4">
            <!-- Cartes stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-gpj-600">{{ stats.today }}</p>
                    <p class="text-xs text-gpj-400">Aujourd'hui</p>
                </div>
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ stats.creations }}</p>
                    <p class="text-xs text-gpj-400">Créations</p>
                </div>
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-amber-600">{{ stats.modifications }}</p>
                    <p class="text-xs text-gpj-400">Modifications</p>
                </div>
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-red-600">{{ stats.suppressions }}</p>
                    <p class="text-xs text-gpj-400">Suppressions</p>
                </div>
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ stats.phase_changes }}</p>
                    <p class="text-xs text-gpj-400">Changements phase</p>
                </div>
                <div class="bg-white rounded-xl border border-gpj-200 p-3 text-center">
                    <p class="text-2xl font-bold text-gpj-600">{{ stats.total }}</p>
                    <p class="text-xs text-gpj-400">Total</p>
                </div>
            </div>

            <!-- Filtres -->
            <Card>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Action</label>
                        <select v-model="filtres.action" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Toutes</option>
                            <option v-for="a in actions" :key="a" :value="a">{{ actionLabel(a) }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Type</label>
                        <select v-model="filtres.model_type" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Tous</option>
                            <option v-for="m in modelTypes" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Date début</label>
                        <input v-model="filtres.date_debut" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gpj-500 mb-1">Date fin</label>
                        <input v-model="filtres.date_fin" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="relative max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400 text-sm"></i>
                        <input
                            v-model="filtres.search"
                            type="text"
                            placeholder="Rechercher dans la description..."
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            @input="appliquerFiltres"
                        />
                    </div>
                </div>
            </Card>

            <!-- Liste des logs -->
            <Card padding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gpj-50 text-gpj-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Date</th>
                                <th class="px-4 py-3 text-left font-semibold">Utilisateur</th>
                                <th class="px-4 py-3 text-left font-semibold">Action</th>
                                <th class="px-4 py-3 text-left font-semibold">Description</th>
                                <th class="px-4 py-3 text-left font-semibold">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gpj-50 transition-colors">
                                <td class="px-4 py-3 text-xs text-gpj-400 whitespace-nowrap">
                                    {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-gpj-100 flex items-center justify-center text-gpj-600 text-xs font-bold">
                                            {{ log.user?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gpj-800">{{ log.user?.name || 'Système' }}</p>
                                            <p class="text-xs text-gpj-400">{{ log.user?.role || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="actionVariant(log.action)" size="sm">{{ actionLabel(log.action) }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-xs text-gpj-600 max-w-xs truncate">{{ log.description }}</td>
                                <td class="px-4 py-3 text-xs text-gpj-400 font-mono">{{ log.ip_address }}</td>
                            </tr>
                            <tr v-if="!logs.data?.length">
                                <td colspan="5" class="px-4 py-12 text-center text-gpj-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucun log trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-gpj-100 gap-3">
                    <p class="text-sm text-gpj-400">{{ logs.from }}-{{ logs.to }} sur {{ logs.total }}</p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in logs.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                link.active ? 'bg-gpj-500 text-white' : 'text-gpj-600 hover:bg-gpj-100',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const props = defineProps({
    logs: Object,
    stats: Object,
    actions: Array,
    modelTypes: Array,
    filters: Object,
});

const filtres = reactive({
    action: props.filters?.action || '',
    model_type: props.filters?.model_type || '',
    date_debut: props.filters?.date_debut || '',
    date_fin: props.filters?.date_fin || '',
    search: props.filters?.search || '',
});

let timeout = null;
const appliquerFiltres = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('historique.index'), filtres, { preserveState: true, replace: true });
    }, 300);
};

const actionLabel = (a) => {
    const labels = {
        create: 'Création', update: 'Modification', delete: 'Suppression',
        phase_change: 'Changement phase', login: 'Connexion', logout: 'Déconnexion',
        password_reset: 'Réinit. MDP', password_reset_request: 'Demande MDP',
    };
    return labels[a] || a;
};

const actionVariant = (a) => {
    const variants = {
        create: 'info', update: 'warning', delete: 'danger',
        phase_change: 'success', login: 'neutral', logout: 'neutral',
        password_reset: 'info', password_reset_request: 'info',
    };
    return variants[a] || 'default';
};
</script>

<script>export default { layout: null };</script>