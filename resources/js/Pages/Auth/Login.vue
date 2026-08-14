<template>
    <GuestLayout title="Connexion" subtitle="Accédez à votre espace GPJ">
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gpj-700 mb-1">Email</label>
                <div class="relative">
                    <i class="pi pi-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        placeholder="exemple@gpj.mil"
                        class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
            </div>

            <!-- Mot de passe -->
            <div>
                <label for="password" class="block text-sm font-medium text-gpj-700 mb-1">Mot de passe</label>
                <div class="relative">
                    <i class="pi pi-lock absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400"></i>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        placeholder="Votre mot de passe"
                        class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500 focus:border-gpj-500"
                    />
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gpj-400 hover:text-gpj-600" @click="showPassword = !showPassword">
                        <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
            </div>

            <!-- Remember + Forgot -->
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gpj-600 cursor-pointer">
                    <input type="checkbox" v-model="form.remember" class="rounded border-slate-400 text-gpj-500 focus:ring-gpj-500" />
                    Se souvenir de moi
                </label>
                <Link :href="route('password.request')" class="text-sm text-gpj-500 hover:text-gpj-700 font-medium">
                    Mot de passe oublié ?
                </Link>
            </div>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full py-2.5 bg-slate-500 text-white font-semibold rounded-lg hover:bg-gpj-600 focus:ring-2 focus:ring-gpj-500 focus:ring-offset-2 transition-colors disabled:opacity-50 cursor-pointer"
            >
                <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                Se connecter
            </button>

            <!-- Register link -->
            <p class="text-center text-sm text-gpj-400">
                Pas encore de compte ?
                <Link :href="route('register')" class="text-gpj-500 hover:text-gpj-700 font-medium">Créer un compte</Link>
            </p>
        </form>
    </GuestLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>