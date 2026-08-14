<template>
    <div class="min-h-screen bg-slate-50 transition-colors duration-300">
        <!-- Hero -->
        <div class="bg-gradient-to-br from-gpj-800 via-gpj-700 to-gpj-600 text-white">
            <div class="max-w-6xl mx-auto px-4 md:px-6 py-12 md:py-20 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 md:w-20 md:h-20 bg-white/20 backdrop-blur rounded-2xl mb-4 md:mb-6 shadow-lg">
                    <i class="pi pi-shield text-white text-2xl md:text-3xl"></i>
                </div>
                <h1 class="text-3xl md:text-5xl font-extrabold mb-3 md:mb-4 tracking-tight">GPJ</h1>
                <p class="text-lg md:text-xl text-gpj-200 mb-1 md:mb-2">Gestion des Procédures Judiciaires Militaires</p>
                <p class="text-sm md:text-base text-gpj-300 max-w-2xl mx-auto mb-6 md:mb-8 px-2">
                    Application sécurisée de gestion et de suivi des procédures judiciaires
                    pour les forces armées.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                    <Link
                        :href="route('login')"
                        class="w-full sm:w-auto px-6 py-3 bg-white text-gpj-700 font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-md text-center"
                    >
                        <i class="pi pi-sign-in mr-2"></i> Connexion
                    </Link>
                    <Link
                        :href="route('register')"
                        class="w-full sm:w-auto px-6 py-3 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition-colors text-center"
                    >
                        <i class="pi pi-user-plus mr-2"></i> Inscription
                    </Link>
                </div>
            </div>
        </div>

        <!-- Caractéristiques -->
        <div class="max-w-6xl mx-auto px-4 md:px-6 py-12 md:py-20">
            <div class="text-center mb-8 md:mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gpj-800 mb-2 md:mb-3">Fonctionnalités clés</h2>
                <p class="text-sm md:text-base text-gpj-400">Tout ce dont vous avez besoin pour gérer les procédures judiciaires</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">
                <div class="text-center p-6 md:p-8 bg-white rounded-xl border border-slate-300 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="pi pi-gavel text-gpj-600 text-lg md:text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gpj-800 mb-2 text-sm md:text-base">Suivi des Procédures</h3>
                    <p class="text-xs md:text-sm text-gpj-400">Suivez chaque procédure de l'ordre de poursuite jusqu'au jugement.</p>
                </div>
                <div class="text-center p-6 md:p-8 bg-white rounded-xl border border-slate-300 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="pi pi-print text-gpj-600 text-lg md:text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gpj-800 mb-2 text-sm md:text-base">Casier Judiciaire</h3>
                    <p class="text-xs md:text-sm text-gpj-400">Générez et imprimez le casier judiciaire de chaque militaire.</p>
                </div>
                <div class="text-center p-6 md:p-8 bg-white rounded-xl border border-slate-300 shadow-sm hover:shadow-md transition-all duration-200 sm:col-span-2 lg:col-span-1">
                    <div class="w-12 h-12 md:w-14 md:h-14 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-3 md:mb-4">
                        <i class="pi pi-chart-bar text-gpj-600 text-lg md:text-xl"></i>
                    </div>
                    <h3 class="font-bold text-gpj-800 mb-2 text-sm md:text-base">Statistiques</h3>
                    <p class="text-xs md:text-sm text-gpj-400">Tableaux de bord complets avec filtres par date, infraction, unité.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>