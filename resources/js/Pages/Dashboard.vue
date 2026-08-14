<template>
    <AuthenticatedLayout title="Tableau de Bord" subtitle="Statistiques et analyses">
        <div class="space-y-6">
            <!-- ====================================================== -->
            <!-- CARTES STATS PRINCIPALES                               -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard label="Total Procédures" :value="stats.total_procedures" icon="pi pi-folder" icon-bg="bg-slate-100" icon-color="#2d5a3d" />
                <StatCard label="En Ordre de Poursuite" :value="stats.en_ordre_poursuite" icon="pi pi-hammer" icon-bg="bg-amber-100" icon-color="#b45309" />
                <StatCard label="En Mise à Disposition" :value="stats.en_mise_disposition" icon="pi pi-lock" icon-bg="bg-red-100" icon-color="#dc2626" />
                <StatCard label="En Communiqué" :value="stats.en_communique" icon="pi pi-bell" icon-bg="bg-sky-100" icon-color="#0284c7" />
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <StatCard label="Militaires" :value="totalMilitaires" icon="pi pi-users" icon-bg="bg-slate-100" icon-color="#2d5a3d" />
                <StatCard label="Infractions" :value="totalInfractions" icon="pi pi-list" icon-bg="bg-purple-100" icon-color="#7c3aed" />
                <StatCard label="En cours" :value="totalProceduresEnCours" icon="pi pi-clock" icon-bg="bg-amber-100" icon-color="#b45309" />
                <StatCard label="Ce mois" :value="stats.total_mois" icon="pi pi-calendar" icon-bg="bg-emerald-100" icon-color="#059669" />
            </div>

            <!-- ====================================================== -->
            <!-- STATS LIEU DE COMMISSION                               -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <StatCard 
                    label="Organique" 
                    :value="statsLieuCommission?.organique || 0" 
                    icon="pi pi-building" 
                    icon-bg="bg-blue-100" 
                    icon-color="#1e40af"
                    :subtext="'Procédures en organique'"
                />
                <StatCard 
                    label="Opération" 
                    :value="statsLieuCommission?.operation || 0" 
                    icon="pi pi-globe" 
                    icon-bg="bg-green-100" 
                    icon-color="#065f46"
                    :subtext="'Procédures en opération'"
                />
                <StatCard 
                    label="Non défini" 
                    :value="statsLieuCommission?.non_defini || 0" 
                    icon="pi pi-question-circle" 
                    icon-bg="bg-gray-100" 
                    icon-color="#6b7280"
                    :subtext="'Lieu non défini'"
                />
            </div>

            <!-- ====================================================== -->
            <!-- STATS CIVILS VS MILITAIRES                            -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <StatCard 
                        label="Procédures avec Militaires" 
                        :value="totalProceduresAvecMilitaire" 
                        icon="pi pi-users" 
                        icon-bg="bg-slate-100" 
                        icon-color="#2d5a3d"
                        :subtext="totalMilitairesImpliques + ' militaires impliqués'"
                    />
                    <StatCard 
                        label="Procédures avec Civils" 
                        :value="totalProceduresAvecCivil" 
                        icon="pi pi-user" 
                        icon-bg="bg-purple-100" 
                        icon-color="#7c3aed"
                        :subtext="totalCivilsImpliques + ' civils impliqués'"
                    />
                </div>
                
                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800">👥 Répartition par type de personnel</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="item in statsParTypePersonnel" :key="item.type_personnel" 
                             class="p-4 rounded-lg text-center"
                             :class="item.bg_color">
                            <i :class="[item.icon, 'text-2xl block mb-2', item.text_color]"></i>
                            <p class="text-sm font-medium" :class="item.text_color">{{ item.label }}</p>
                            <p class="text-2xl font-bold" :class="item.text_color">{{ item.nombre_personnes }}</p>
                            <p class="text-xs text-slate-500">{{ item.nombre_procedures }} procédure(s)</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ====================================================== -->
            <!-- STATS INFRACTIONS PAR TYPE DE PERSONNEL              -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card>
                    <h3 class="text-sm font-semibold text-slate-800 mb-3">⚖️ Infractions par type de personnel</h3>
                    <div class="space-y-2">
                        <div v-for="item in statsInfractionsParType" :key="item.type_personnel" 
                             class="flex items-center justify-between p-3 rounded-lg"
                             :class="item.type_personnel === 'militaire' ? 'bg-slate-50' : 'bg-purple-50'">
                            <span class="text-sm font-medium" :class="item.type_personnel === 'militaire' ? 'text-slate-800' : 'text-purple-700'">
                                {{ item.label }}
                            </span>
                            <span class="text-lg font-bold" :class="item.type_personnel === 'militaire' ? 'text-slate-700' : 'text-purple-600'">
                                {{ item.nombre }}
                            </span>
                        </div>
                    </div>
                </Card>

                <Card>
                    <h3 class="text-sm font-semibold text-slate-800 mb-3">📋 Fautes par type de personnel</h3>
                    <div class="space-y-2">
                        <div v-for="item in statsFautesParType" :key="item.type_personnel" 
                             class="flex items-center justify-between p-3 rounded-lg"
                             :class="item.type_personnel === 'militaire' ? 'bg-amber-50' : 'bg-purple-50'">
                            <span class="text-sm font-medium" :class="item.type_personnel === 'militaire' ? 'text-amber-700' : 'text-purple-700'">
                                {{ item.label }}
                            </span>
                            <span class="text-lg font-bold" :class="item.type_personnel === 'militaire' ? 'text-amber-600' : 'text-purple-600'">
                                {{ item.nombre }}
                            </span>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ====================================================== -->
            <!-- DÉTAIL LIEU DE COMMISSION PAR PHASE                   -->
            <!-- ====================================================== -->
            <Card title="📊 Détail par lieu de commission et phase">
                <div v-if="statsLieuCommissionDetail && statsLieuCommissionDetail.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="lieu in statsLieuCommissionDetail" :key="lieu.lieu" class="border border-slate-300 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-slate-800 mb-3">
                            {{ lieu.lieu }}
                            <span class="text-xs text-slate-500 ml-2">(Total: {{ lieu.total }})</span>
                        </h4>
                        <div class="space-y-2">
                            <div v-for="phase in lieu.phases" :key="phase.phase" 
                                 class="flex items-center justify-between p-2 bg-slate-50 rounded">
                                <span class="text-sm text-slate-700">{{ phase.phase }}</span>
                                <span class="text-sm font-bold text-slate-800">{{ phase.nombre }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune donnée disponible</p>
            </Card>

            <!-- ====================================================== -->
            <!-- ÉVOLUTION DES PROCÉDURES                              -->
            <!-- ====================================================== -->
            <Card title="📈 Évolution des procédures (12 mois)">
                <div class="h-64">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </Card>

            <!-- ====================================================== -->
            <!-- STATS PAR JOUR (7 derniers jours)                    -->
            <!-- ====================================================== -->
            <Card title="📊 Procédures par jour (7 derniers jours)">
                <div class="grid grid-cols-7 gap-2">
                    <div v-for="item in statsParJour" :key="item.jour" class="text-center p-3 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">{{ item.jour_label }}</p>
                        <p class="text-xl font-bold text-slate-700">{{ item.total }}</p>
                    </div>
                </div>
            </Card>

            <!-- ====================================================== -->
            <!-- STATS PARQUETS                                        -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                    <h3 class="text-sm font-semibold text-slate-800 mb-3">📋 Parquets</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center p-2 bg-slate-50 rounded">
                            <span class="text-sm">Total parquets</span>
                            <span class="font-bold text-slate-700">{{ totalParquets }}</span>
                        </div>
                        <div class="flex justify-between items-center p-2 bg-blue-50 rounded">
                            <span class="text-sm">Parquets militaires</span>
                            <span class="font-bold text-blue-600">{{ totalParquetsMilitaires }}</span>
                        </div>
                        <div class="flex justify-between items-center p-2 bg-purple-50 rounded">
                            <span class="text-sm">Parquets droit commun</span>
                            <span class="font-bold text-purple-600">{{ totalParquetsDroitCommun }}</span>
                        </div>
                    </div>
                </Card>

                <Card class="md:col-span-2">
                    <h3 class="text-sm font-semibold text-slate-800 mb-3">🏛️ Procédures par type de parquet</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="item in statsParquets" :key="item.parquet_type" class="p-3 rounded-lg text-center" 
                            :class="item.parquet_type === 'militaire' ? 'bg-blue-50' : 'bg-purple-50'">
                            <p class="text-sm font-medium" :class="item.parquet_type === 'militaire' ? 'text-blue-700' : 'text-purple-700'">
                                {{ item.label }}
                            </p>
                            <p class="text-2xl font-bold" :class="item.parquet_type === 'militaire' ? 'text-blue-600' : 'text-purple-600'">
                                {{ item.nombre }}
                            </p>
                            <p class="text-xs text-slate-500">procédure(s)</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- ====================================================== -->
            <!-- DETAIL DES PARQUETS                                   -->
            <!-- ====================================================== -->
            <Card title="📊 Détail des parquets">
                <div class="mb-3 flex flex-wrap gap-2">
                    <span class="text-xs text-slate-500">Chaque parquet avec son nombre de procédures associées</span>
                </div>
                <div v-if="statsParquetDetail.length" class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold">#</th>
                                <th class="px-3 py-2 text-left font-semibold">Nom du parquet</th>
                                <th class="px-3 py-2 text-left font-semibold">Type</th>
                                <th class="px-3 py-2 text-left font-semibold">Localisation</th>
                                <th class="px-3 py-2 text-center font-semibold">Procédures</th>
                                <th class="px-3 py-2 text-left font-semibold">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gpj-100">
                            <tr v-for="(item, index) in statsParquetDetail" :key="item.id" class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2 text-center text-slate-500">{{ index + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">
                                    {{ item.nom }}
                                    <span v-if="item.nombre_procedures === 0" class="text-[10px] text-slate-500 ml-1">(inactif)</span>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="item.type === 'militaire' ? 'info' : 'primary'" size="sm">
                                        {{ item.type_label }}
                                    </Badge>
                                </td>
                                <td class="px-3 py-2 text-slate-700">{{ item.localisation || '-' }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full" 
                                        :class="item.nombre_procedures > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-400'">
                                        <i v-if="item.nombre_procedures > 0" class="pi pi-folder text-xs"></i>
                                        <span class="font-bold">{{ item.nombre_procedures }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <Badge :variant="item.is_active ? 'success' : 'neutral'" size="sm">
                                        {{ item.active_label }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucun parquet enregistré</p>
            </Card>

            <!-- ====================================================== -->
            <!-- TOP INFRACTIONS + TOP FAUTES                          -->
            <!-- ====================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800">Infractions les plus fréquentes</h3>
                        <a :href="route('exports.top-infractions')" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1 shrink-0"><i class="pi pi-download text-xs"></i> Excel</a>
                    </div>
                    <div v-if="topInfractions.length" class="space-y-2">
                        <div v-for="(inf, i) in topInfractions" :key="i" class="flex items-center gap-2 text-sm">
                            <span class="w-6 h-6 rounded bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold shrink-0">{{ i + 1 }}</span>
                            <span class="flex-1 truncate">{{ inf.libelle }}</span>
                            <div class="w-20 bg-gray-100 rounded-full h-2"><div class="bg-slate-500 h-2 rounded-full" :style="{ width: (inf.nombre / maxInfractions * 100) + '%' }"></div></div>
                            <span class="font-bold text-slate-700 w-8 text-right">{{ inf.nombre }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune donnée</p>
                </Card>

                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-slate-800">Fautes militaires les plus fréquentes</h3>
                        <a :href="route('exports.top-fautes')" class="text-xs text-slate-600 hover:text-slate-800 flex items-center gap-1 shrink-0"><i class="pi pi-download text-xs"></i> Excel</a>
                    </div>
                    <div v-if="topFautes.length" class="space-y-2">
                        <div v-for="(faute, i) in topFautes" :key="i" class="flex items-center gap-2 text-sm">
                            <span class="w-6 h-6 rounded bg-amber-100 text-amber-600 flex items-center justify-center text-xs font-bold shrink-0">{{ i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <span class="truncate block">{{ faute.libelle }}</span>
                                <span v-if="faute.categorie" class="text-[10px] text-slate-500">Catégorie: {{ faute.categorie }}</span>
                            </div>
                            <div class="w-20 bg-gray-100 rounded-full h-2"><div class="bg-amber-500 h-2 rounded-full" :style="{ width: (faute.nombre / maxFautes * 100) + '%' }"></div></div>
                            <span class="font-bold text-amber-600 w-8 text-right">{{ faute.nombre }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune donnée</p>
                </Card>
            </div>

            <!-- ====================================================== -->
            <!-- STATS FAUTES PAR CATÉGORIE                            -->
            <!-- ====================================================== -->
            <Card title="📂 Fautes par catégorie">
                <div v-if="statsFautesParCategorie && statsFautesParCategorie.length" class="space-y-4">
                    <div v-for="categorie in statsFautesParCategorie" :key="categorie.categorie_id" 
                         class="border border-slate-300 rounded-lg p-3 hover:shadow-sm transition-shadow">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" :class="categorie.categorie_id ? 'bg-slate-500' : 'bg-gray-400'"></span>
                                {{ categorie.categorie_libelle }}
                            </h4>
                            <Badge variant="info" size="sm">
                                {{ categorie.total }} occurrence(s)
                            </Badge>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="faute in categorie.fautes" :key="faute.faute_id" 
                                  class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-50 rounded-full text-xs border border-slate-200">
                                {{ faute.libelle }}
                                <span class="text-slate-500 font-bold ml-1">({{ faute.nombre }})</span>
                            </span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune faute enregistrée</p>
            </Card>

            <!-- ====================================================== -->
            <!-- PROCÉDURES RÉCENTES                                   -->
            <!-- ====================================================== -->
            <Card title="Procédures récentes">
                <div v-if="proceduresRecentes.length" class="space-y-2">
                    <Link v-for="p in proceduresRecentes" :key="p.id" :href="route('procedures.show', p.id)" class="flex items-center gap-2 p-2 rounded hover:bg-slate-50 text-sm">
                        <span class="font-medium text-slate-600">{{ p.numero_procedure }}</span>
                        <span class="text-slate-500 text-xs truncate">{{ p.militaire?.nom }} {{ p.militaire?.prenoms }}</span>
                        <span v-if="p.parquet" class="text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                            {{ p.parquet.nom }}
                        </span>
                        <Badge :variant="phaseVariant(p.phase)" size="sm">{{ (p.phase || '').replace(/_/g, ' ') }}</Badge>
                        <Badge v-if="p.lieu_commission" variant="neutral" size="sm" class="text-[10px]">
                            {{ p.lieu_commission }}
                        </Badge>
                    </Link>
                </div>
                <p v-else class="text-sm text-slate-500 py-4 text-center">Aucune procédure récente</p>
            </Card>

            <!-- ====================================================== -->
            <!-- STATS INFRACTIONS                                     -->
            <!-- ====================================================== -->
            <div class="border-t border-slate-300 pt-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4">📊 Infractions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <StatBarCard title="Par Armée" :data="statsParArmee" color="bg-slate-500" :export-url="route('exports.infractions.armee')" />
                    <StatBarCard title="Par Catégorie de Grade" :data="statsParCategorieGrade" color="bg-amber-500" :export-url="route('exports.infractions.categorie-grade')" />
                    <StatBarCard title="Par Grade (Top 15)" :data="statsParGrade" color="bg-red-500" label-key="libelle" :export-url="route('exports.infractions.grade')" />
                    <StatPieCard title="Par Genre" :data="statsParGenre" :export-url="route('exports.infractions.genre')" />
                </div>
            </div>

            <!-- ====================================================== -->
            <!-- STATS FAUTES MILITAIRES                               -->
            <!-- ====================================================== -->
            <div class="border-t border-slate-300 pt-4">
                <h2 class="text-lg font-bold text-slate-900 mb-4">📊 Fautes Militaires</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <StatBarCard title="Par Armée" :data="statsFautesParArmee" color="bg-slate-500" :export-url="route('exports.fautes.armee')" />
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
import { onMounted, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    stats: Object,
    statsLieuCommission: { type: Object, default: () => ({ organique: 0, operation: 0, non_defini: 0 }) },
    statsLieuCommissionDetail: { type: Array, default: () => [] },
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
    statsFautesParCategorie: { type: Array, default: () => [] },
    statsParquets: { type: Array, default: () => [] },
    statsParquetDetail: { type: Array, default: () => [] },
    totalParquets: { type: Number, default: 0 },
    totalParquetsMilitaires: { type: Number, default: 0 },
    totalParquetsDroitCommun: { type: Number, default: 0 },
    statsParTypePersonnel: { type: Array, default: () => [] },
    statsTypePersonnel: { type: Object, default: () => ({}) },
    totalProceduresAvecMilitaire: { type: Number, default: 0 },
    totalProceduresAvecCivil: { type: Number, default: 0 },
    totalMilitairesImpliques: { type: Number, default: 0 },
    totalCivilsImpliques: { type: Number, default: 0 },
    statsInfractionsParType: { type: Array, default: () => [] },
    statsFautesParType: { type: Array, default: () => [] },
    evolutionProcedures: { type: Array, default: () => [] },
    statsParJour: { type: Array, default: () => [] },
});

const phaseVariant = (p) => { 
    const map = { 
        'Ordre de Poursuite': 'warning', 
        'Mise à Disposition': 'danger', 
        'Communiqué': 'info' 
    };
    return map[p] || 'default';
};

// ============================================================
// GRAPHIQUE D'ÉVOLUTION
// ============================================================
let evolutionChart = null;

const createEvolutionChart = () => {
    const canvas = document.getElementById('evolutionChart');
    if (!canvas) {
        console.warn('Canvas evolutionChart non trouvé');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (evolutionChart) {
        evolutionChart.destroy();
    }
    
    const labels = props.evolutionProcedures.map(item => item.mois_label || item.mois);
    const totals = props.evolutionProcedures.map(item => item.total || 0);
    const cloturees = props.evolutionProcedures.map(item => item.cloturees || 0);
    
    if (labels.length === 0) {
        console.warn('Aucune donnée pour le graphique');
        return;
    }
    
    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total procédures',
                    data: totals,
                    borderColor: '#2d5a3d',
                    backgroundColor: 'rgba(45, 90, 61, 0.1)',
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Clôturées',
                    data: cloturees,
                    borderColor: '#b45309',
                    backgroundColor: 'rgba(180, 83, 9, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderDash: [5, 5],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { size: 11 },
                        boxWidth: 12,
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
    
    console.log('✅ Graphique créé avec succès');
};

onMounted(() => {
    nextTick(() => {
        setTimeout(createEvolutionChart, 300);
    });
});

// Recreate chart when data changes
watch(() => props.evolutionProcedures, () => {
    setTimeout(createEvolutionChart, 300);
}, { deep: true });
</script>
<script>export default { layout: null };</script>