<template>
    <AuthenticatedLayout title="Tableau de Bord" subtitle="Statistiques et analyses">
        <div class="space-y-6">
            <!-- Cartes stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard label="Total Procédures" :value="stats.total_procedures" icon="pi pi-folder" iconBg="bg-gpj-100" iconColor="#2d5a3d" />
                <StatCard label="En Ordre de Poursuite" :value="stats.en_ordre_poursuite" icon="pi pi-hammer" iconBg="bg-amber-100" iconColor="#b45309" />
                <StatCard label="En Mise à Disposition" :value="stats.en_mise_disposition" icon="pi pi-lock" iconBg="bg-red-100" iconColor="#dc2626" />
                <StatCard label="En Communiqué" :value="stats.en_communique" icon="pi pi-bell" iconBg="bg-sky-100" iconColor="#0284c7" />
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard label="Militaires" :value="totalMilitaires" icon="pi pi-users" iconBg="bg-gpj-100" iconColor="#2d5a3d" />
                <StatCard label="Infractions" :value="totalInfractions" icon="pi pi-list" iconBg="bg-purple-100" iconColor="#7c3aed" />
                <StatCard label="En cours" :value="totalProceduresEnCours" icon="pi pi-clock" iconBg="bg-amber-100" iconColor="#b45309" />
                <StatCard label="Ce mois" :value="stats.total_mois" icon="pi pi-calendar" iconBg="bg-emerald-100" iconColor="#059669" />
            </div>

            <!-- Top infractions + Top fautes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gpj-700">Infractions les plus fréquentes</h3>
                        <a :href="route('exports.top-infractions')" class="text-xs text-gpj-500 hover:text-gpj-700 flex items-center gap-1 shrink-0"><i class="pi pi-download text-xs"></i> Excel</a>
                    </div>
                    <div v-if="topInfractions.length" class="space-y-2">
                        <div v-for="(inf, i) in topInfractions" :key="i" class="flex items-center gap-2 text-sm">
                            <span class="w-6 h-6 rounded bg-gpj-100 text-gpj-600 flex items-center justify-center text-xs font-bold shrink-0">{{ i + 1 }}</span>
                            <span class="flex-1 truncate">{{ inf.libelle }}</span>
                            <div class="w-20 bg-gray-100 rounded-full h-2"><div class="bg-gpj-500 h-2 rounded-full" :style="{ width: (inf.nombre / maxInfractions * 100) + '%' }"></div></div>
                            <span class="font-bold text-gpj-600 w-8 text-right">{{ inf.nombre }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucune donnée</p>
                </Card>

                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gpj-700">Fautes militaires les plus fréquentes</h3>
                        <a :href="route('exports.top-fautes')" class="text-xs text-gpj-500 hover:text-gpj-700 flex items-center gap-1 shrink-0"><i class="pi pi-download text-xs"></i> Excel</a>
                    </div>
                    <div v-if="topFautes.length" class="space-y-2">
                        <div v-for="(faute, i) in topFautes" :key="i" class="flex items-center gap-2 text-sm">
                            <span class="w-6 h-6 rounded bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">{{ i + 1 }}</span>
                            <span class="flex-1 truncate">{{ faute.libelle }}</span>
                            <div class="w-20 bg-gray-100 rounded-full h-2"><div class="bg-amber-500 h-2 rounded-full" :style="{ width: (faute.nombre / maxFautes * 100) + '%' }"></div></div>
                            <span class="font-bold text-amber-600 w-8 text-right">{{ faute.nombre }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucune donnée</p>
                </Card>
            </div>

            <!-- Procédures récentes -->
            <Card title="Procédures récentes">
                <div v-if="proceduresRecentes.length" class="space-y-2">
                    <Link v-for="p in proceduresRecentes" :key="p.id" :href="route('procedures.show', p.id)" class="flex items-center gap-2 p-2 rounded hover:bg-gpj-50 text-sm">
                        <span class="font-medium text-gpj-500">{{ p.numero_procedure }}</span>
                        <span class="text-gpj-400 text-xs truncate">{{ p.militaire?.nom }} {{ p.militaire?.prenoms }}</span>
                        <Badge :variant="phaseVariant(p.phase)" size="sm">{{ (p.phase || '').replace(/_/g, ' ') }}</Badge>
                    </Link>
                </div>
                <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucune procédure récente</p>
            </Card>

            <!-- Stats Infractions -->
            <div class="border-t border-gpj-200 pt-4">
                <h2 class="text-lg font-bold text-gpj-800 mb-4">📊 Statistiques des Infractions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <StatBarCard title="Par Armée" :data="statsParArmee" color="bg-gpj-500" :export-url="route('exports.infractions.armee')" />
                    <StatBarCard title="Par Catégorie de Grade" :data="statsParCategorieGrade" color="bg-amber-500" :export-url="route('exports.infractions.categorie-grade')" />
                    <StatBarCard title="Par Grade (Top 15)" :data="statsParGrade" color="bg-red-500" label-key="libelle" :export-url="route('exports.infractions.grade')" />
                    <StatPieCard title="Par Genre" :data="statsParGenre" :export-url="route('exports.infractions.genre')" />
                </div>
            </div>

            <!-- Stats Fautes Militaires -->
            <div class="border-t border-gpj-200 pt-4">
                <h2 class="text-lg font-bold text-gpj-800 mb-4">⚠️ Statistiques des Fautes Militaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <StatBarCard title="Par Armée" :data="statsFautesParArmee" color="bg-gpj-500" :export-url="route('exports.fautes.armee')" />
                    <StatBarCard title="Par Catégorie de Grade" :data="statsFautesParCategorieGrade" color="bg-amber-500" :export-url="route('exports.fautes.categorie-grade')" />
                    <StatBarCard title="Par Grade (Top 15)" :data="statsFautesParGrade" color="bg-red-500" label-key="libelle" :export-url="route('exports.fautes.grade')" />
                    <StatPieCard title="Par Genre" :data="statsFautesParGenre" :export-url="route('exports.fautes.genre')" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';
import StatCard from '@/Components/GPJ/StatCard.vue';
import StatBarCard from '@/Components/Dashboard/StatBarCard.vue';
import StatPieCard from '@/Components/Dashboard/StatPieCard.vue';

const props = defineProps({
    stats: Object, 
    topInfractions: Array, 
    maxInfractions: Number,
    topFautes: Array, 
    maxFautes: Number, 
    proceduresRecentes: Array,
    totalMilitaires: Number, 
    totalInfractions: Number, 
    totalProceduresEnCours: Number,
    statsParArmee: Array, 
    statsParCategorieGrade: Array, 
    statsParGrade: Array, 
    statsParGenre: Array,
    statsFautesParArmee: Array, 
    statsFautesParCategorieGrade: Array, 
    statsFautesParGrade: Array, 
    statsFautesParGenre: Array,
});

const phaseVariant = (p) => { 
    const map = { 
        'Ordre de Poursuite': 'warning', 
        'Mise à Disposition': 'danger', 
        'Communiqué': 'info' 
    };
    return map[p] || 'default';
};
</script>
<script>export default { layout: null };</script>