<template>
    <AuthenticatedLayout title="Modifier l'utilisateur" subtitle="Mettre à jour le compte">
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
                            <option v-for="(label, key) in roles" :key="key" :value="key">
                                {{ key }} - {{ label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-xs text-red-500 mt-1">{{ errors.role }}</p>
                        <p class="text-xs text-gpj-400 dark:text-gpj-500 mt-1">
                            Rôle actuel : <span class="font-medium">{{ roles[user.role] }}</span>
                        </p>
                    </div>

                    <!-- Mot de passe (optionnel) -->
                    <div class="border-t border-gpj-100 dark:border-gpj-800 pt-4">
                        <p class="text-sm text-gpj-500 dark:text-gpj-400 mb-3">
                            Changer le mot de passe 
                            <span class="text-xs text-gpj-400">(laisser vide pour conserver l'actuel)</span>
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                                    Nouveau mot de passe
                                </label>
                                <input 
                                    v-model="form.password" 
                                    type="password" 
                                    class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                                    placeholder="••••••••" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gpj-700 dark:text-gpj-300 mb-1">
                                    Confirmer
                                </label>
                                <input 
                                    v-model="form.password_confirmation" 
                                    type="password" 
                                    class="w-full rounded-lg border border-gpj-200 dark:border-gpj-700 dark:bg-gpj-800 dark:text-white px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" 
                                    placeholder="••••••••" 
                                />
                            </div>
                        </div>
                        <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
                    </div>

                    <!-- Informations sur l'utilisateur -->
                    <div class="bg-gpj-50 dark:bg-gpj-800/50 rounded-lg p-4 text-sm text-gpj-600 dark:text-gpj-300">
                        <p class="font-medium mb-1">ℹ️ Informations</p>
                        <p class="text-xs">
                            Utilisateur créé le {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                            à {{ new Date(user.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}
                        </p>
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
                            Mettre à jour
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
import { Card } from '@/Components/GPJ';

const props = defineProps({
    user: Object,
    roles: Object,
    errors: Object,
});

const form = reactive({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

const processing = ref(false);

const submit = () => {
    processing.value = true;
    router.patch(route('users.update', props.user.id), form, {
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