<template>
    <AuthenticatedLayout title="Nouvel Utilisateur" subtitle="Créer un compte utilisateur">
        <div class="max-w-2xl mx-auto">
            <Card>
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                            placeholder="Jean Dupont" 
                            required 
                        />
                        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                            placeholder="jean@exemple.com" 
                            required 
                        />
                        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- Rôle -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                            Rôle <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="form.role" 
                            class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                            required
                        >
                            <option value="">Sélectionner un rôle</option>
                            <option v-for="(label, key) in roles" :key="key" :value="key">
                                {{ key }} - {{ label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-xs text-red-500 mt-1">{{ errors.role }}</p>
                    </div>

                    <!-- Mot de passe -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                            placeholder="••••••••" 
                            required 
                        />
                        <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
                    </div>

                    <!-- Confirmation -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.password_confirmation" 
                            type="password" 
                            class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                            placeholder="••••••••" 
                            required 
                        />
                    </div>

                    <!-- Informations sur les rôles -->
                    <div class="bg-gpj-50 dark:bg-gpj-800/50 rounded-lg p-4 text-sm">
                        <p class="font-medium text-gpj-700 dark:text-gpj-300 mb-2">📋 Rôles et responsabilités :</p>
                        <ul class="space-y-2 text-xs">
                            <li class="flex items-start gap-2">
                                <Badge variant="danger" size="sm">ADMIN</Badge>
                                <span class="text-gpj-600 dark:text-gpj-400">
                                    <span class="font-medium">Administrateur</span> - Supervision haute, gestion des utilisateurs, validation ultime
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Badge variant="warning" size="sm">CDD</Badge>
                                <span class="text-gpj-600 dark:text-gpj-400">
                                    <span class="font-medium">Chef de Division</span> - Gestion administrative de la division, affectation des dossiers
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Badge variant="info" size="sm">CDS</Badge>
                                <span class="text-gpj-600 dark:text-gpj-400">
                                    <span class="font-medium">Chef de Section</span> - Supervision des agents, validation des étapes de la procédure
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Badge variant="primary" size="sm">CDB</Badge>
                                <span class="text-gpj-600 dark:text-gpj-400">
                                    <span class="font-medium">Chef de Bureau</span> - Gestion du bureau, accès en consultation avancée
                                </span>
                            </li>
                            <li class="flex items-start gap-2">
                                <Badge variant="neutral" size="sm">ADS</Badge>
                                <span class="text-gpj-600 dark:text-gpj-400">
                                    <span class="font-medium">Agent de Saisie</span> - Alimentation de la base, création des dossiers, numérisation
                                </span>
                            </li>
                        </ul>
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center gap-3 pt-3 border-t border-gpj-100 dark:border-gpj-800">
                        <Link 
                            :href="route('users.index')" 
                            class="px-4 py-2.5 border border-gpj-200 dark:border-gpj-700 text-gpj-600 dark:text-gpj-300 text-sm rounded-lg hover:bg-gpj-50 dark:hover:bg-gpj-800 transition-colors"
                        >
                            Annuler
                        </Link>
                        <button 
                            type="submit" 
                            :disabled="processing" 
                            class="flex-1 px-4 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 disabled:opacity-50 transition-colors"
                        >
                            <i v-if="processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Créer l'utilisateur
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';

const props = defineProps({
    roles: Object,
    errors: Object,
});

const form = reactive({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.post(route('users.store'), form, {
        onError: () => { 
            processing.value = false; 
        },
        onSuccess: () => { 
            processing.value = false; 
        },
    });
};
</script>
<script>export default { layout: null };</script>