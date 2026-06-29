<template>
    <GuestLayout title="Mot de passe oublié" subtitle="Réinitialisez votre mot de passe">
        <div v-if="status" class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gpj-700 mb-1">Email</label>
                <div class="relative">
                    <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="exemple@gpj.mil"
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-gpj-500 text-white font-semibold rounded-lg hover:bg-gpj-600 focus:ring-2 focus:ring-gpj-500 focus:ring-offset-2 transition-colors disabled:opacity-50 cursor-pointer"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                Envoyer le lien de réinitialisation
            </button>

            <p class="text-center text-sm text-gpj-400">
                <Link :href="route('login')" class="text-gpj-500 hover:text-gpj-700 font-medium">Retour à la connexion</Link>
            </p>
        </form>
    </GuestLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>