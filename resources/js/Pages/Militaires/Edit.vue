<template>
    <AuthenticatedLayout title="Modifier Militaire" :subtitle="`${militaire.matricule} - ${militaire.nom} ${militaire.prenoms}`">
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
                            <input v-model="form.matricule" type="text" placeholder="MIL-2026-XXX"
                                class="w-full px-3 py-2.5 rounded-lg border border-gpj-200 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <p class="text-xs text-gpj-400 mt-1">Laissez vide pour conserver l'actuel</p>
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
                <div class="flex items-center justify-between pt-4 border-t border-gpj-100">
                    <button v-if="isSD" type="button" @click="confirmDelete"
                        class="px-4 py-2 border border-red-300 text-red-600 text-sm rounded-lg hover:bg-red-50 transition-colors cursor-pointer">
                        <i class="pi pi-trash mr-2"></i> Supprimer
                    </button>
                    <div v-else></div>
                    <div class="flex gap-3">
                        <Link :href="route('militaires.show', militaire.id)"
                            class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 transition-colors">
                            Annuler
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer">
                            <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </Card>

        <!-- Modal confirmation suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <i class="pi pi-exclamation-triangle text-red-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800">Confirmer la suppression</h3>
                </div>
                <p class="text-sm text-gpj-600 mb-2">Vous êtes sur le point de supprimer :</p>
                <p class="text-sm font-bold text-gpj-800 mb-2">{{ militaire.matricule }} - {{ militaire.nom }} {{ militaire.prenoms }}</p>
                <p class="text-sm text-red-500 mb-6">⚠️ Cette action est irréversible. Les militaires ayant des procédures judiciaires ne peuvent pas être supprimés.</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button>
                    <button @click="deleteMilitaire" :disabled="deleteProcessing" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50 cursor-pointer">
                        <i v-if="deleteProcessing" class="pi pi-spin pi-spinner mr-1"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

const page = usePage();
const props = defineProps({
    militaire: Object,
    categoriesGrades: Array,
    armees: Array,
});

const isSD = computed(() => page.props.auth.user.role === 'SD');

const selectedCategorie = ref(props.militaire.grade?.categorie_grade_id || '');

const filteredGrades = computed(() => {
    if (!selectedCategorie.value) return [];
    const cat = props.categoriesGrades.find(c => c.id == selectedCategorie.value);
    return cat ? cat.grades : [];
});

const onCategorieChange = () => {
    form.grade_id = '';
};

const formatDateForInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const form = useForm({
    matricule: props.militaire.matricule,
    nom: props.militaire.nom,
    prenoms: props.militaire.prenoms,
    date_naissance: formatDateForInput(props.militaire.date_naissance),
    grade_id: props.militaire.grade_id || '',
    unite: props.militaire.unite || '',
    adresse: props.militaire.adresse || '',
    telephone: props.militaire.telephone || '',
    statut: props.militaire.statut,
    genre: props.militaire.genre || '',
    armee: props.militaire.armee || '',
});

const submit = () => {
    form.patch(route('militaires.update', props.militaire.id));
};

const showDeleteModal = ref(false);
const deleteProcessing = ref(false);

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteMilitaire = () => {
    deleteProcessing.value = true;
    router.delete(route('militaires.destroy', props.militaire.id), {
        onSuccess: () => {
            deleteProcessing.value = false;
            showDeleteModal.value = false;
        },
        onError: () => {
            deleteProcessing.value = false;
        },
    });
};
</script>

<script>export default { layout: null };</script>