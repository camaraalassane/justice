<template>
    <AuthenticatedLayout title="Modifier Infraction" :subtitle="infraction.libelle">
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
                            class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        />
                        <p class="mt-1 text-xs text-gpj-400">
                            Format : INF-CRxx (Criminelle), INF-DExx (Délictuelle), INF-COxx (Contravention)
                        </p>
                        <p v-if="form.errors.code_infraction" class="mt-1 text-sm text-red-500">{{ form.errors.code_infraction }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Gravité (1-5) <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.gravite"
                            required
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
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
                        class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    />
                    <p v-if="form.errors.libelle" class="mt-1 text-sm text-red-500">{{ form.errors.libelle }}</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gpj-700 mb-1">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="Description détaillée et référence aux textes..."
                        class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"
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
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="c in classifications" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gpj-400">
                            <span v-if="form.classification === 'Criminelle'">Crimes : meurtre, viol, trahison, désertion en guerre</span>
                            <span v-else-if="form.classification === 'Délictuelle'">Délits : vol, escroquerie, abandon de poste</span>
                            <span v-else-if="form.classification === 'Contravention'">Contraventions : trouble, négligence, retard</span>
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
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option value="">Sélectionner</option>
                            <option v-for="n in natures" :key="n" :value="n">{{ n }}</option>
                        </select>
                        <p v-if="form.errors.nature" class="mt-1 text-sm text-red-500">{{ form.errors.nature }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gpj-100">
                    <button
                        type="button"
                        @click="confirmDelete"
                        class="px-4 py-2 border border-red-300 text-red-600 text-sm rounded-lg hover:bg-red-50 transition-colors cursor-pointer"
                    >
                        <i class="pi pi-trash mr-2"></i> Supprimer
                    </button>
                    <div class="flex gap-3">
                        <Link
                            :href="route('infractions.index')"
                            class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 transition-colors"
                        >
                            Annuler
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer"
                        >
                            <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </Card>

        <!-- Modal confirmation -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                <h3 class="text-lg font-bold text-gpj-800 mb-2">Confirmer la suppression</h3>
                <p class="text-sm text-gpj-600 mb-2">
                    Êtes-vous sûr de vouloir supprimer l'infraction <strong>{{ infraction.libelle }}</strong> ?
                </p>
                <p class="text-sm text-red-500 mb-6">
                    ⚠️ Cette action est irréversible. Les infractions utilisées dans des procédures ne peuvent pas être supprimées.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer"
                    >
                        Annuler
                    </button>
                    <button
                        type="button"
                        @click="deleteInfraction"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 cursor-pointer"
                    >
                        <i class="pi pi-trash mr-2"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

const props = defineProps({
    infraction: Object,
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

const showDeleteModal = ref(false);

const form = useForm({
    code_infraction: props.infraction.code_infraction,
    libelle: props.infraction.libelle,
    description: props.infraction.description || '',
    classification: props.infraction.classification,
    nature: props.infraction.nature,
    gravite: props.infraction.gravite,
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
    form.patch(route('infractions.update', props.infraction.id));
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteInfraction = () => {
    router.delete(route('infractions.destroy', props.infraction.id), {
        onSuccess: () => showDeleteModal.value = false,
    });
};
</script>

<script>
export default { layout: null };
</script>