<template>
    <AuthenticatedLayout title="Historique" subtitle="Journal des activités">
        <div class="space-y-4">
            <!-- Cartes stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-slate-700">{{ stats.today }}</p>
                    <p class="text-xs text-slate-500">Aujourd'hui</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-blue-600">{{ stats.creations }}</p>
                    <p class="text-xs text-slate-500">Créations</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-amber-600">{{ stats.modifications }}</p>
                    <p class="text-xs text-slate-500">Modifications</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-red-600">{{ stats.suppressions }}</p>
                    <p class="text-xs text-slate-500">Suppressions</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-purple-600">{{ stats.phase_changes }}</p>
                    <p class="text-xs text-slate-500">Changements phase</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center shadow-sm">
                    <p class="text-2xl font-bold text-slate-700">{{ stats.total }}</p>
                    <p class="text-xs text-slate-500">Total</p>
                </div>
            </div>

            <!-- Filtres -->
            <Card>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Action</label>
                        <select v-model="filtres.action" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-sm text-slate-800 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Toutes</option>
                            <option v-for="a in actions" :key="a" :value="a">{{ actionLabel(a) }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Type</label>
                        <select v-model="filtres.model_type" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-sm text-slate-800 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm">
                            <option value="">Tous</option>
                            <option v-for="m in modelTypes" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Date début</label>
                        <input v-model="filtres.date_debut" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-sm text-slate-800 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Date fin</label>
                        <input v-model="filtres.date_fin" type="date" @change="appliquerFiltres" class="w-full rounded-lg border border-slate-300 text-sm text-slate-800 py-2 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="relative max-w-sm">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input
                            v-model="filtres.search"
                            type="text"
                            placeholder="Rechercher dans la description..."
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500 shadow-sm"
                            @input="appliquerFiltres"
                        />
                    </div>
                </div>
            </Card>

            <!-- Liste des logs -->
            <Card padding>
                <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-260px)]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 border-b-2 border-slate-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Date</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Utilisateur</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Action</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">Description</th>
                                <th class="px-2 py-1.5 text-left font-semibold text-xs uppercase tracking-wide">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-2 py-1.5 text-xs text-slate-500 whitespace-nowrap">
                                    {{ new Date(log.created_at).toLocaleString('fr-FR') }}
                                </td>
                                <td class="px-2 py-1.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 text-xs font-bold">
                                            {{ log.user?.name?.charAt(0) || '?' }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-slate-800">{{ log.user?.name || 'Système' }}</p>
                                            <p class="text-xs text-slate-400">{{ log.user?.role || '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5">
                                    <Badge :variant="actionVariant(log.action)" size="sm">{{ actionLabel(log.action) }}</Badge>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-600 max-w-xs truncate">{{ log.description }}</td>
                                <td class="px-2 py-1.5 text-xs text-slate-400 font-mono">{{ log.ip_address }}</td>
                            </tr>
                            <tr v-if="!logs.data?.length">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                    <i class="pi pi-inbox text-3xl mb-2 block"></i>
                                    Aucun log trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-slate-100 gap-3">
                    <p class="text-sm text-slate-500">{{ logs.from }}-{{ logs.to }} sur {{ logs.total }}</p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in logs.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                link.active ? 'bg-gpj-500 text-white' : 'text-slate-600 hover:bg-slate-100',
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