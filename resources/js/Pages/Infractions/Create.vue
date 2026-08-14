<template>
    <AuthenticatedLayout title="Nouvelle Infraction" subtitle="Ajouter une infraction à la nomenclature">
        <Card class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Code + Gravité -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.code_infraction"
                            type="text"
                            required
                            placeholder="INF-CR01, INF-DE01, INF-CO01"
                            class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        />
                        <p class="mt-1 text-xs text-gpj-400">Format : INF-CRxx (Criminelle), INF-DExx (Délictuelle), INF-COxx (Contravention)</p>
                        <p v-if="form.errors.code_infraction" class="mt-1 text-sm text-red-500">{{ form.errors.code_infraction }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Gravité (1-5) <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.gravite"
                            required
                            class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option v-for="i in 5" :key="i" :value="i">{{ i }} - {{ graviteLabel(i) }}</option>
                        </select>
                        <p v-if="form.errors.gravite" class="mt-1 text-sm text-red-500">{{ form.errors.gravite }}</p>
                    </div>
                </div>

                <!-- Libellé -->
                <div>
                    <label class="block text-sm font-medium text-gpj-700 mb-1">
                        Libellé <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.libelle"
                        type="text"
                        required
                        placeholder="Ex: Désertion en temps de paix"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    />
                    <p v-if="form.errors.libelle" class="mt-1 text-sm text-red-500">{{ form.errors.libelle }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gpj-700 mb-1">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Description détaillée de l'infraction et référence aux textes (ex: Art. 44 Code justice militaire)..."
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    ></textarea>
                </div>

                <!-- Classification + Nature -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Classification <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.classification"
                            required
                            class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option value="">Sélectionner une classification</option>
                            <option v-for="c in classifications" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gpj-400">
                            <span v-if="form.classification === 'Criminelle'">Infractions les plus graves (meurtre, viol, trahison, désertion en temps de guerre)</span>
                            <span v-else-if="form.classification === 'Délictuelle'">Infractions de gravité moyenne (vol, escroquerie, abandon de poste)</span>
                            <span v-else-if="form.classification === 'Contravention'">Infractions mineures (trouble à l'ordre, négligence, retard)</span>
                        </p>
                        <p v-if="form.errors.classification" class="mt-1 text-sm text-red-500">{{ form.errors.classification }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Nature <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.nature"
                            required
                            class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option value="">Sélectionner une nature</option>
                            <option v-for="n in natures" :key="n" :value="n">{{ n }}</option>
                        </select>
                        <p v-if="form.errors.nature" class="mt-1 text-sm text-red-500">{{ form.errors.nature }}</p>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <Link
                        :href="route('infractions.index')"
                        class="px-4 py-2 border border-slate-300 text-gpj-600 text-sm rounded-lg hover:bg-slate-50 transition-colors"
                    >
                        Annuler
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer"
                    >
                        <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                        Créer l'infraction
                    </button>
                </div>
            </form>
        </Card>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

defineProps({
    classifications: {
        type: Array,
        default: () => ['Criminelle', 'Délictuelle', 'Contravention'],
    },
    natures: {
        type: Array,
        default: () => [
            'Atteinte à l\'honneur',
            'Atteinte aux biens',
            'Manquement à la discipline',
            'Infraction au droit commun',
            'Désertion',
            'Trahison',
        ],
    },
});

const form = useForm({
    code_infraction: '',
    libelle: '',
    description: '',
    classification: '',
    nature: '',
    gravite: 1,
});

const graviteLabel = (i) => {
    const labels = {
        1: 'Très faible',
        2: 'Faible',
        3: 'Moyenne',
        4: 'Grave',
        5: 'Très grave',
    };
    return labels[i] || '';
};

const submit = () => {
    form.post(route('infractions.store'));
};
</script>

<script>
export default { layout: null };
</script>