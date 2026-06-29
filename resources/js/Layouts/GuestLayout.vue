<template>
    <div class="min-h-screen flex flex-col bg-gpj-50 transition-colors duration-300">
        <!-- Barre supérieure -->
        <div class="bg-gpj-800 py-2 md:py-3 px-4 md:px-6 flex items-center justify-center flex-shrink-0">
            <div class="flex items-center gap-2 text-white text-xs md:text-sm">
                <i class="pi pi-shield"></i>
                <span class="font-medium">GPJ</span>
                <span class="text-gpj-300 hidden sm:inline">| Gestion des Procédures Judiciaires</span>
            </div>
        </div>

        <!-- Contenu centré -->
        <div class="flex-1 flex items-center justify-center p-4 md:p-6">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <div class="text-center mb-6 md:mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 md:w-16 md:h-16 bg-gpj-500 rounded-2xl mb-3 md:mb-4 shadow-lg">
                        <i class="pi pi-shield text-white text-xl md:text-2xl"></i>
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold text-gpj-800">
                        <slot name="title">GPJ</slot>
                    </h1>
                    <p class="text-gpj-400 mt-1 text-xs md:text-sm">
                        <slot name="subtitle">Application de gestion judiciaire</slot>
                    </p>
                </div>

                <!-- Carte du formulaire -->
                <div class="bg-white rounded-xl border border-gpj-200 shadow-sm p-5 md:p-6 transition-colors duration-300">
                    <slot />
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="py-3 md:py-4 text-center text-xs text-gpj-400 border-t border-gpj-100 flex-shrink-0 transition-colors duration-300">
            © {{ new Date().getFullYear() }} GPJ - Ministère de la Défense
        </div>
    </div>
</template>