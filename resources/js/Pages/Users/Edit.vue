<template>
    <AuthenticatedLayout title="Modifier l'utilisateur" subtitle="Mettre à jour le compte">
        <div class="max-w-2xl mx-auto">
            <Card>
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm" 
                            required 
                        />
                        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            class="w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm" 
                            required 
                        />
                        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- Rôle -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Rôle <span class="text-red-500">*</span>
                        </label>
                        <select 
                            v-model="form.role" 
                            class="w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm" 
                            required
                        >
                            <option v-for="(label, key) in roles" :key="key" :value="key">
                                {{ key }} - {{ label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-xs text-red-500 mt-1">{{ errors.role }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            Rôle actuel : <span class="font-medium">{{ roles[user.role] }}</span>
                        </p>
                    </div>

                    <!-- Mot de passe (optionnel) -->
                    <div class="border-t border-slate-200 pt-4">
                        <p class="text-sm text-slate-600 mb-3">
                            Changer le mot de passe 
                            <span class="text-xs text-slate-400">(laisser vide pour conserver l'actuel)</span>
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Nouveau mot de passe
                                </label>
                                <input 
                                    v-model="form.password" 
                                    type="password" 
                                    class="w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm" 
                                    placeholder="••••••••" 
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Confirmer
                                </label>
                                <input 
                                    v-model="form.password_confirmation" 
                                    type="password" 
                                    class="w-full rounded-lg border border-slate-300 text-slate-900 placeholder-slate-400 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500 shadow-sm" 
                                    placeholder="••••••••" 
                                />
                            </div>
                        </div>
                        <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
                    </div>

                    <!-- Informations sur l'utilisateur -->
                    <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-600 border border-slate-200">
                        <p class="font-semibold text-slate-800 mb-1">ℹ️ Informations</p>
                        <p class="text-xs">
                            Utilisateur créé le {{ new Date(user.created_at).toLocaleDateString('fr-FR') }}
                            à {{ new Date(user.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) }}
                        </p>
                    </div>

                    <!-- Boutons -->
                    <div class="flex items-center gap-3 pt-3 border-t border-slate-200">
                        <Link 
                            :href="route('users.index')" 
                            class="px-4 py-2.5 border border-slate-300 text-slate-600 text-sm rounded-lg hover:bg-slate-50 transition-colors"
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