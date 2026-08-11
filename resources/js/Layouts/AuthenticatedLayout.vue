<template>
    <div class="min-h-screen bg-gpj-50 flex transition-colors duration-300">
        <!-- Overlay mobile -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity duration-300"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'w-64 bg-gpj-800 text-white flex flex-col fixed inset-y-0 left-0 z-50 shadow-xl transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="px-5 py-4 border-b border-gpj-700 shrink-0">
                <Link :href="route('dashboard')" class="flex items-center gap-3" @click="closeSidebar">
                    <div class="w-9 h-9 bg-gpj-500 rounded-lg flex items-center justify-center shrink-0">
                        <i class="pi pi-shield text-white text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-lg font-bold text-white leading-tight truncate">GPJ</h1>
                        <p class="text-xs text-gpj-300 truncate">Gestion des Procédures Judiciaires</p>
                    </div>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <NavLink 
                    :href="route('dashboard')" 
                    :active="route().current('dashboard')" 
                    icon="pi pi-th-large" 
                    @click="closeSidebar"
                >
                    Tableau de Bord
                </NavLink>

                <NavLink 
                    :href="route('procedures.index')" 
                    :active="route().current('procedures.*')" 
                    icon="pi pi-book" 
                    @click="closeSidebar"
                >
                    Procédures
                </NavLink>

                <!-- CHANGEMENT: "Militaires" → "Personnels" -->
                <NavLink 
                    :href="route('militaires.index')" 
                    :active="route().current('militaires.*')" 
                    icon="pi pi-users" 
                    @click="closeSidebar"
                >
                    Personnels
                </NavLink>

                <NavLink 
                    :href="route('infractions.index')" 
                    :active="route().current('infractions.*')" 
                    icon="pi pi-list" 
                    @click="closeSidebar"
                >
                    Infractions
                </NavLink>

                <NavLink 
                    :href="route('historique.index')" 
                    :active="route().current('historique.*')" 
                    icon="pi pi-clock" 
                    @click="closeSidebar"
                >
                    Historique
                </NavLink>

                <!-- Gestion des Utilisateurs - UNIQUEMENT pour ADMIN (Administrateur) -->
                <div v-if="canManageUsers" class="pt-2 mt-2 border-t border-gpj-700/50">
                    <NavLink 
                        :href="route('users.index')" 
                        :active="route().current('users.*')" 
                        icon="pi pi-user-plus" 
                        @click="closeSidebar"
                        class="bg-gpj-700/30 hover:bg-gpj-700"
                    >
                        <span class="flex items-center gap-2">
                            Utilisateurs
                            <span class="text-[10px] bg-gpj-500 text-white px-2 py-0.5 rounded-full font-normal">ADMIN</span>
                        </span>
                    </NavLink>
                </div>

                <NavLink 
                    :href="route('profile.edit')" 
                    :active="route().current('profile.*')" 
                    icon="pi pi-cog" 
                    @click="closeSidebar"
                >
                    Paramètres
                </NavLink>
            </nav>

            <!-- User info -->
            <div class="p-4 border-t border-gpj-700 shrink-0">
                <div class="flex items-center gap-3">
                    <div 
                        class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 text-white"
                        :class="avatarColor"
                    >
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ user.name }}</p>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDotColor"></span>
                            <p class="text-xs text-gpj-300 truncate">{{ roleLabel }}</p>
                        </div>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="mt-3 w-full text-xs text-gpj-300 hover:text-white flex items-center gap-2 py-1.5 px-2 rounded hover:bg-gpj-700 transition-colors"
                >
                    <i class="pi pi-sign-out text-xs"></i>
                    Déconnexion
                </Link>
            </div>
        </aside>

        <!-- Contenu principal -->
        <div class="flex-1 flex flex-col min-h-screen lg:ml-64">
            <!-- Top bar -->
            <header class="bg-white border-b border-gpj-200 px-3 md:px-6 py-3 flex items-center justify-between sticky top-0 z-30 transition-colors duration-300">
                <div class="flex items-center gap-2 md:gap-3 min-w-0">
                    <button
                        class="lg:hidden w-10 h-10 flex items-center justify-center rounded-lg text-gpj-600 hover:bg-gpj-100 transition-colors shrink-0"
                        @click="toggleSidebar"
                        :aria-label="sidebarOpen ? 'Fermer le menu' : 'Ouvrir le menu'"
                    >
                        <i :class="sidebarOpen ? 'pi pi-times' : 'pi pi-bars'" class="text-lg"></i>
                    </button>
                    <div class="min-w-0">
                        <h2 class="text-base md:text-xl font-bold text-gpj-800 truncate">{{ pageTitle }}</h2>
                        <p class="text-xs text-gpj-400 mt-0.5 hidden sm:block truncate">{{ pageSubtitle }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-1 md:gap-2 shrink-0">
                    <!-- Badge rôle -->
                    <span 
                        class="hidden md:inline-flex items-center gap-1.5 text-xs px-3 py-1 rounded-full font-medium"
                        :class="roleBadgeClass"
                    >
                        <span class="w-1.5 h-1.5 rounded-full" :class="roleDotClass"></span>
                        {{ roleLabel }}
                    </span>
                    <span class="hidden md:inline text-xs text-gpj-400 bg-gpj-100 px-2 py-1 rounded-full whitespace-nowrap">
                        {{ formattedDate }}
                    </span>
                    <button
                        class="w-10 h-10 flex items-center justify-center rounded-lg text-gpj-500 hover:bg-gpj-100 transition-colors"
                        @click="toggleDarkMode"
                        :title="isDark ? 'Mode clair' : 'Mode sombre'"
                    >
                        <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'" class="text-lg"></i>
                    </button>
                </div>
            </header>

            <!-- Contenu de la page -->
            <main class="flex-1 p-3 md:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

const page = usePage();
const sidebarOpen = ref(false);
const isDark = ref(false);

// ==================== UTILISATEUR CONNECTÉ ====================
const user = computed(() => page.props.auth.user);

// ==================== RÔLES ET LIBELLÉS ====================
const roles = {
    ADMIN: 'Administrateur',
    CDD: 'Chef de Division',
    CDS: 'Chef de Section',
    CDB: 'Chef de Bureau',
    ADS: 'Agent de Saisie',
};

const roleLabel = computed(() => {
    return roles[user.value?.role] || user.value?.role || 'Utilisateur';
});

const userInitials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name.charAt(0).toUpperCase();
});

// ==================== PERMISSIONS ====================
const canManageUsers = computed(() => {
    return user.value?.role === 'ADMIN';
});

// ==================== STYLES PAR RÔLE ====================
const roleColors = {
    ADMIN: { bg: 'bg-red-100', text: 'text-red-700', dot: 'bg-red-500', avatar: 'bg-red-600' },
    CDD: { bg: 'bg-amber-100', text: 'text-amber-700', dot: 'bg-amber-500', avatar: 'bg-amber-600' },
    CDS: { bg: 'bg-blue-100', text: 'text-blue-700', dot: 'bg-blue-500', avatar: 'bg-blue-600' },
    CDB: { bg: 'bg-purple-100', text: 'text-purple-700', dot: 'bg-purple-500', avatar: 'bg-purple-600' },
    ADS: { bg: 'bg-gray-100', text: 'text-gray-700', dot: 'bg-gray-500', avatar: 'bg-gray-600' },
};

const defaultColor = { bg: 'bg-gray-100', text: 'text-gray-700', dot: 'bg-gray-500', avatar: 'bg-gray-600' };

const roleBadgeClass = computed(() => {
    const colors = roleColors[user.value?.role] || defaultColor;
    return `${colors.bg} ${colors.text}`;
});

const roleDotClass = computed(() => {
    const colors = roleColors[user.value?.role] || defaultColor;
    return colors.dot;
});

const avatarColor = computed(() => {
    const colors = roleColors[user.value?.role] || defaultColor;
    return colors.avatar;
});

const statusDotColor = computed(() => {
    const colors = roleColors[user.value?.role] || defaultColor;
    return colors.dot;
});

// ==================== PAGE ====================
const pageTitle = computed(() => page.props.title || 'GPJ');
const pageSubtitle = computed(() => page.props.subtitle || '');

const formattedDate = computed(() => {
    return new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

// ==================== MÉTHODES ====================
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    updateDarkMode();
};

const updateDarkMode = () => {
    if (isDark.value) {
        document.documentElement.classList.add('gpj-dark');
        localStorage.setItem('gpj-dark-mode', 'true');
    } else {
        document.documentElement.classList.remove('gpj-dark');
        localStorage.setItem('gpj-dark-mode', 'false');
    }
};

// ==================== LIFECYCLE ====================
onMounted(() => {
    // Récupérer la préférence dark mode
    const saved = localStorage.getItem('gpj-dark-mode');
    if (saved === 'true') {
        isDark.value = true;
        document.documentElement.classList.add('gpj-dark');
    } else if (saved === 'false') {
        isDark.value = false;
        document.documentElement.classList.remove('gpj-dark');
    }
});

// Fermer la sidebar lors d'un changement de page
watch(() => page.url, () => {
    sidebarOpen.value = false;
});

// Gérer le scroll quand la sidebar est ouverte
watch(sidebarOpen, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});
</script>