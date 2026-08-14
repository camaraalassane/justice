<template>
    <AuthenticatedLayout title="Paramètres" subtitle="Gestion du profil utilisateur">
        <div class="max-w-3xl mx-auto space-y-6">
            <!-- Infos profil -->
            <Card title="Informations du profil">
                <form @submit.prevent="updateProfile" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gpj-700 mb-1">Nom complet</label>
                        <div class="relative">
                            <i class="pi pi-user absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                            <input
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                required
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                            />
                        </div>
                        <p v-if="profileForm.errors.name" class="mt-1 text-sm text-red-500">{{ profileForm.errors.name }}</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gpj-700 mb-1">Email</label>
                        <div class="relative">
                            <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                            <input
                                id="email"
                                v-model="profileForm.email"
                                type="email"
                                required
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                            />
                        </div>
                        <p v-if="profileForm.errors.email" class="mt-1 text-sm text-red-500">{{ profileForm.errors.email }}</p>
                    </div>

                    <!-- Rôle (affichage seul) -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Rôle</label>
                        <div class="flex items-center gap-2">
                            <span class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-gpj-600 text-sm font-bold">
                                {{ $page.props.auth.user.role }}
                            </span>
                            <span class="text-gpj-600 text-sm font-medium">{{ roleLabel }}</span>
                        </div>
                    </div>

                    <div v-if="profileForm.recentlySuccessful" class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                        Profil mis à jour avec succès.
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="px-6 py-2.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer"
                        >
                            <i v-if="profileForm.processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </Card>

            <!-- Mot de passe -->
            <Card title="Changer le mot de passe">
                <form @submit.prevent="updatePassword" class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gpj-700 mb-1">Mot de passe actuel</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                            <input
                                id="current_password"
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                            />
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.current_password }}</p>
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gpj-700 mb-1">Nouveau mot de passe</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                            <input
                                id="new_password"
                                v-model="passwordForm.password"
                                type="password"
                                required
                                placeholder="Minimum 8 caractères"
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                            />
                        </div>
                        <p v-if="passwordForm.errors.password" class="mt-1 text-sm text-red-500">{{ passwordForm.errors.password }}</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gpj-700 mb-1">Confirmer le mot de passe</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                            <input
                                id="password_confirmation"
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                            />
                        </div>
                    </div>

                    <div v-if="passwordForm.recentlySuccessful" class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                        Mot de passe changé avec succès.
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="px-6 py-2.5 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer"
                        >
                            <i v-if="passwordForm.processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Changer le mot de passe
                        </button>
                    </div>
                </form>
            </Card>

            <!-- Supprimer le compte -->
            <Card>
                <template #header>
                    <div class="px-6 py-4 border-b border-red-200 bg-red-50 rounded-t-xl">
                        <h3 class="text-lg font-semibold text-red-700 flex items-center gap-2">
                            <i class="pi pi-exclamation-triangle"></i> Zone dangereuse
                        </h3>
                    </div>
                </template>
                <p class="text-sm text-gpj-600 mb-4">
                    Une fois votre compte supprimé, toutes ses données seront définitivement effacées. Cette action est irréversible.
                </p>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors cursor-pointer"
                >
                    <i class="pi pi-trash mr-2"></i> Supprimer mon compte
                </button>
            </Card>
        </div>

        <!-- Modal de confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-lg font-bold text-gpj-800 mb-2">Confirmer la suppression</h3>
                <p class="text-sm text-gpj-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.
                </p>
                <form @submit.prevent="deleteUser" class="space-y-4">
                    <div>
                        <label for="delete_password" class="block text-sm font-medium text-gpj-700 mb-1">Mot de passe actuel</label>
                        <input
                            id="delete_password"
                            v-model="deleteForm.password"
                            type="password"
                            required
                            class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        />
                        <p v-if="deleteForm.errors.password" class="mt-1 text-sm text-red-500">{{ deleteForm.errors.password }}</p>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button
                            type="button"
                            @click="showDeleteModal = false"
                            class="px-4 py-2 border border-slate-300 text-gpj-600 text-sm rounded-lg hover:bg-slate-50 transition-colors cursor-pointer"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="deleteForm.processing"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 cursor-pointer"
                        >
                            <i v-if="deleteForm.processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

const page = usePage();
const showDeleteModal = ref(false);

const roleLabel = computed(() => {
    const roles = {
        ADMIN: 'Administrateur',
        CDD: 'Chef de Division',
        CDS: 'Chef de Section',
        CDB: 'Chef de Bureau',
        ADS: 'Agent de Saisie',
    };
    return roles[page.props.auth.user.role] || '';
});

// Formulaire profil
const profileForm = useForm({
    name: page.props.auth.user.name,
    email: page.props.auth.user.email,
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'));
};

// Formulaire mot de passe
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        onSuccess: () => passwordForm.reset(),
    });
};

// Formulaire suppression
const deleteForm = useForm({
    password: '',
});

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteUser = () => {
    deleteForm.delete(route('profile.destroy'), {
        onSuccess: () => showDeleteModal.value = false,
    });
};
</script>

<script>
export default { layout: null };
</script>