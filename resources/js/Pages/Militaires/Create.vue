<template>
    <AuthenticatedLayout title="Nouveau Militaire" subtitle="Ajouter un militaire dans la base">
        <Card class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Identité -->
                <div class="border-b border-gpj-100 pb-4">
                    <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide mb-3">Identité</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Nom <span class="text-red-500">*</span></label>
                            <input v-model="form.nom" type="text" required placeholder="COULIBALY"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <p v-if="form.errors.nom" class="mt-1 text-sm text-red-500">{{ form.errors.nom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Prénoms <span class="text-red-500">*</span></label>
                            <input v-model="form.prenoms" type="text" required placeholder="Amadou"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <p v-if="form.errors.prenoms" class="mt-1 text-sm text-red-500">{{ form.errors.prenoms }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Matricule</label>
                            <input v-model="form.matricule" type="text" placeholder="MIL-2026-XXX (automatique si vide)"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <p class="text-xs text-gpj-400 mt-1">Laissez vide pour génération automatique</p>
                            <p v-if="form.errors.matricule" class="mt-1 text-sm text-red-500">{{ form.errors.matricule }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Date de naissance</label>
                            <input v-model="form.date_naissance" type="date"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <p v-if="form.errors.date_naissance" class="mt-1 text-sm text-red-500">{{ form.errors.date_naissance }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Genre</label>
                            <select v-model="form.genre"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                                <option value="">Sélectionner</option>
                                <option value="Masculin">Masculin</option>
                                <option value="Féminin">Féminin</option>
                            </select>
                            <p v-if="form.errors.genre" class="mt-1 text-sm text-red-500">{{ form.errors.genre }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Armée/Service</label>
                            <select v-model="form.armee"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                                <option value="">Sélectionner</option>
                                <option v-for="a in armees" :key="a" :value="a">{{ a }}</option>
                            </select>
                            <p v-if="form.errors.armee" class="mt-1 text-sm text-red-500">{{ form.errors.armee }}</p>
                        </div>
                    </div>
                </div>

                <!-- Affectation (optionnelle) -->
                <div class="border-b border-gpj-100 pb-4">
                    <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide mb-3">Affectation <span class="text-xs font-normal text-gpj-400">(optionnel)</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Catégorie</label>
                            <select v-model="selectedCategorie" @change="onCategorieChange"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                                <option value="">Sélectionner une catégorie</option>
                                <option v-for="cat in categoriesGrades" :key="cat.id" :value="cat.id">{{ cat.libelle }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Grade</label>
                            <select v-model="form.grade_id" :disabled="!selectedCategorie"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500 disabled:opacity-50">
                                <option value="">Sélectionner un grade</option>
                                <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">
                                    {{ grade.libelle }} ({{ grade.abreviation }})
                                </option>
                            </select>
                            <p v-if="form.errors.grade_id" class="mt-1 text-sm text-red-500">{{ form.errors.grade_id }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Unité</label>
                        <input v-model="form.unite" type="text" placeholder="Ex: 1ère Compagnie, Bataillon XYZ..."
                            class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        <p v-if="form.errors.unite" class="mt-1 text-sm text-red-500">{{ form.errors.unite }}</p>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Statut</label>
                        <select v-model="form.statut"
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="Actif">Actif</option>
                            <option value="Suspendu">Suspendu</option>
                            <option value="Déserteur">Déserteur</option>
                            <option value="Radié">Radié</option>
                        </select>
                    </div>
                </div>

                <!-- Contact (optionnel) -->
                <div>
                    <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide mb-3">Contact <span class="text-xs font-normal text-gpj-400">(optionnel)</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Adresse</label>
                            <textarea v-model="form.adresse" rows="2" placeholder="Adresse personnelle"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">Téléphone</label>
                            <input v-model="form.telephone" type="text" placeholder="+223 XX XX XX XX"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gpj-100">
                    <Link :href="route('militaires.index')"
                        class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 transition-colors">
                        Annuler
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer">
                        <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                        Créer le militaire
                    </button>
                </div>
            </form>
        </Card>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

const props = defineProps({
    categoriesGrades: Array,
    armees: Array,
});

const selectedCategorie = ref('');

const filteredGrades = computed(() => {
    if (!selectedCategorie.value) return [];
    const cat = props.categoriesGrades.find(c => c.id == selectedCategorie.value);
    return cat ? cat.grades : [];
});

const onCategorieChange = () => {
    form.grade_id = '';
};

const form = useForm({
    nom: '',
    prenoms: '',
    matricule: '',
    date_naissance: '',
    grade_id: '',
    unite: '',
    adresse: '',
    telephone: '',
    statut: 'Actif',
    genre: '',
    armee: '',
});

const submit = () => {
    form.post(route('militaires.store'));
};
</script>

<script>export default { layout: null };</script>