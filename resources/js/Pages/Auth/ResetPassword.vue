<template>
    <GuestLayout title="Réinitialisation" subtitle="Définissez votre nouveau mot de passe">
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email (caché mais présent) -->
            <input type="hidden" v-model="form.token" />

            <div>
                <label for="email" class="block text-sm font-medium text-slate-800 mb-1">Email</label>
                <div class="relative">
                    <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="exemple@gpj.mil"
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-800 mb-1">Nouveau mot de passe</label>
                <div class="relative">
                    <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="Minimum 8 caractères"
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                    />
                </div>
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-800 mb-1">Confirmer</label>
                <div class="relative">
                    <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        placeholder="Répétez le mot de passe"
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-slate-500"
                    />
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-gpj-500 text-white font-semibold rounded-lg hover:bg-gpj-600 focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-colors disabled:opacity-50 cursor-pointer"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                Réinitialiser le mot de passe
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>