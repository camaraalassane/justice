<template>
    <AuthenticatedLayout title="Gestion des Utilisateurs" subtitle="Gérer les comptes et les rôles">
        <div class="space-y-4">
            <!-- En-tête avec filtres -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400 text-sm"></i>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Rechercher un utilisateur..."
                            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            @input="appliquerFiltres"
                        />
                    </div>
                    <select 
                        v-model="filters.role" 
                        @change="appliquerFiltres" 
                        class="rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    >
                        <option value="">Tous les rôles</option>
                        <option v-for="(label, key) in roles" :key="key" :value="key">
                            {{ key }} - {{ label }}
                        </option>
                    </select>
                </div>
                <Link 
                    :href="route('users.create')" 
                    class="flex items-center gap-2 px-4 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors shrink-0"
                >
                    <i class="pi pi-plus"></i> 
                    Nouvel utilisateur
                </Link>
            </div>

            <!-- Légende des rôles -->
            <div class="flex flex-wrap gap-3 text-xs bg-white dark:bg-gpj-800/50 rounded-lg p-3 border border-gpj-100 dark:border-gpj-700">
                <span class="font-medium text-gpj-800 dark:text-gpj-400">Rôles :</span>
                <span v-for="(label, key) in roles" :key="key" class="flex items-center gap-1">
                    <Badge :variant="roleVariant(key)" size="sm">{{ key }}</Badge>
                    <span class="text-gpj-700 dark:text-gpj-400">- {{ label }}</span>
                </span>
            </div>

            <!-- Tableau -->
            <Card padding>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gpj-50 dark:bg-gpj-800/50 text-gpj-600 dark:text-gpj-400">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Utilisateur</th>
                                <th class="px-4 py-3 text-left font-semibold">Email</th>
                                <th class="px-4 py-3 text-left font-semibold">Rôle</th>
                                <th class="px-4 py-3 text-left font-semibold">Créé le</th>
                                <th class="px-4 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100 dark:divide-gpj-800">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gpj-50 dark:hover:bg-gpj-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div 
                                            class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                            :class="avatarColor(user.role)"
                                        >
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gpj-800 dark:text-white">{{ user.name }}</p>
                                            <p class="text-xs text-gpj-400 dark:text-gpj-500">ID #{{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gpj-600 dark:text-gpj-300">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    <div>
                                        <Badge :variant="roleVariant(user.role)">
                                            {{ user.role }}
                                        </Badge>
                                        <p class="text-xs text-gpj-400 dark:text-gpj-500 mt-0.5">{{ roles[user.role] }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gpj-400 dark:text-gpj-500">
                                    {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link 
                                            :href="route('users.edit', user.id)" 
                                            class="p-2 text-gpj-400 hover:text-gpj-600 dark:hover:text-gpj-300 hover:bg-gpj-100 dark:hover:bg-gpj-800 rounded-lg transition-colors"
                                            title="Modifier"
                                        >
                                            <i class="pi pi-pencil"></i>
                                        </Link>
                                        <button 
                                            @click="confirmDelete(user)" 
                                            class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg transition-colors"
                                            title="Supprimer"
                                            :disabled="user.id === $page.props.auth.user.id"
                                        >
                                            <i class="pi pi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data?.length">
                                <td colspan="5" class="px-4 py-12 text-center text-gpj-400 dark:text-gpj-500">
                                    <i class="pi pi-users text-3xl mb-2 block"></i>
                                    Aucun utilisateur trouvé
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links?.length > 3" class="flex flex-col sm:flex-row items-center justify-between mt-4 pt-4 border-t border-gpj-100 dark:border-gpj-800 gap-3">
                    <p class="text-sm text-gpj-400 dark:text-gpj-500">
                        {{ users.from }}-{{ users.to }} sur {{ users.total }}
                    </p>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in users.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                link.active ? 'bg-gpj-500 text-white' : 'text-gpj-600 dark:text-gpj-300 hover:bg-gpj-100 dark:hover:bg-gpj-800',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </Card>
        </div>

        <!-- Modal de confirmation de suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 flex items-center justify-center shrink-0">
                        <i class="pi pi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800 dark:text-white">Confirmer la suppression</h3>
                </div>
                <p class="text-sm text-gpj-600 dark:text-gpj-300 mb-6">
                    Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ userToDelete?.name }}</strong> ?
                    <br><span class="text-red-500">Cette action est irréversible.</span>
                </p>
                <div class="flex gap-3 justify-end">
                    <button 
                        @click="showDeleteModal = false" 
                        class="px-4 py-2 border border-gpj-200 dark:border-gpj-700 text-gpj-600 dark:text-gpj-300 text-sm rounded-lg hover:bg-gpj-50 dark:hover:bg-gpj-800 cursor-pointer"
                    >
                        Annuler
                    </button>
                    <button 
                        @click="deleteUser" 
                        :disabled="processing" 
                        class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 disabled:opacity-50 cursor-pointer"
                    >
                        <i v-if="processing" class="pi pi-spin pi-spinner mr-1"></i>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const page = usePage();

const props = defineProps({
    users: Object,
    roles: Object,
    filters: Object,
});

const filters = reactive({
    search: props.filters?.search || '',
    role: props.filters?.role || '',
});

let timeout = null;
const appliquerFiltres = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('users.index'), filters, { preserveState: true, replace: true });
    }, 300);
};

const showDeleteModal = ref(false);
const userToDelete = ref(null);
const processing = ref(false);

const confirmDelete = (user) => {
    if (user.id === page.props.auth.user.id) {
        return;
    }
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (!userToDelete.value) return;
    processing.value = true;
    router.delete(route('users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            processing.value = false;
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
        onError: () => { 
            processing.value = false; 
        },
    });
};

const roleVariant = (role) => {
    const map = { 
        ADMIN: 'danger',   // Administrateur - Rouge
        CDD: 'warning',    // Chef de Division - Orange
        CDS: 'info',       // Chef de Section - Bleu
        CDB: 'primary',    // Observateur - Violet
        ADS: 'neutral'     // Agent de Saisie - Gris
    };
    return map[role] || 'default';
};

const avatarColor = (role) => {
    const map = {
        ADMIN: 'bg-red-500',
        CDD: 'bg-amber-500',
        CDS: 'bg-blue-500',
        CDB: 'bg-purple-500',
        ADS: 'bg-gray-500',
    };
    return map[role] || 'bg-gpj-500';
};
</script>
<script>export default { layout: null };</script>