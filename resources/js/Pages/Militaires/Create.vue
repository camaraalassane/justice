<template>
    <AuthenticatedLayout title="Nouveau Personnel" subtitle="Ajouter un personnel dans la base">
        <Card class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Type de personnel -->
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Type de personnel</h3>
                    <div class="flex gap-4">
                        <label v-for="option in typePersonnelOptions" :key="option.value" class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="radio" 
                                :value="option.value"
                                v-model="form.type_personnel"
                                class="rounded-full border-slate-400 text-slate-600 focus:ring-slate-500"
                            />
                            <span class="text-sm">{{ option.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Identité -->
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Identité</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Nom <span class="text-red-500">*</span></label>
                            <input v-model="form.nom" type="text" required placeholder="COULIBALY"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p v-if="form.errors.nom" class="mt-1 text-sm text-red-500">{{ form.errors.nom }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Prénoms <span class="text-red-500">*</span></label>
                            <input v-model="form.prenoms" type="text" required placeholder="Amadou"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p v-if="form.errors.prenoms" class="mt-1 text-sm text-red-500">{{ form.errors.prenoms }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div v-if="form.type_personnel === 'militaire'">
                            <label class="block text-sm font-medium text-slate-800 mb-1">Matricule</label>
                            <input v-model="form.matricule" type="text" placeholder="MIL-2026-XXX (automatique si vide)"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p class="text-xs text-slate-500 mt-1">Laissez vide pour génération automatique</p>
                            <p v-if="form.errors.matricule" class="mt-1 text-sm text-red-500">{{ form.errors.matricule }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Date de naissance</label>
                            <input v-model="form.date_naissance" type="date"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p v-if="form.errors.date_naissance" class="mt-1 text-sm text-red-500">{{ form.errors.date_naissance }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Lieu de naissance</label>
                            <input v-model="form.lieu_naissance" type="text" placeholder="Ex: Bamako"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p v-if="form.errors.lieu_naissance" class="mt-1 text-sm text-red-500">{{ form.errors.lieu_naissance }}</p>
                        </div>
                    </div>
                    
                    <!-- Filiation -->
                    <div class="mt-4 border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-medium text-slate-700 mb-3">Filiation</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Père -->
                            <div class="border border-slate-200 rounded-lg p-3">
                                <p class="text-xs font-medium text-slate-600 mb-2">Père</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-slate-700 mb-1">Nom</label>
                                        <input v-model="form.nom_pere" type="text" placeholder="Nom du père"
                                            class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-700 mb-1">Prénoms</label>
                                        <input v-model="form.prenoms_pere" type="text" placeholder="Prénoms du père"
                                            class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                                    </div>
                                </div>
                            </div>

                            <!-- Mère -->
                            <div class="border border-slate-200 rounded-lg p-3">
                                <p class="text-xs font-medium text-slate-600 mb-2">Mère</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-slate-700 mb-1">Nom</label>
                                        <input v-model="form.nom_mere" type="text" placeholder="Nom de la mère"
                                            class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-700 mb-1">Prénoms</label>
                                        <input v-model="form.prenoms_mere" type="text" placeholder="Prénoms de la mère"
                                            class="w-full px-2 py-1.5 rounded border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Genre</label>
                            <select v-model="form.genre"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500">
                                <option value="">Sélectionner</option>
                                <option value="Masculin">Masculin</option>
                                <option value="Féminin">Féminin</option>
                            </select>
                            <p v-if="form.errors.genre" class="mt-1 text-sm text-red-500">{{ form.errors.genre }}</p>
                        </div>
                        <div v-if="form.type_personnel === 'civil'">
                            <label class="block text-sm font-medium text-slate-800 mb-1">Profession</label>
                            <input v-model="form.profession" type="text" placeholder="Ex: Enseignant, Médecin..."
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                            <p v-if="form.errors.profession" class="mt-1 text-sm text-red-500">{{ form.errors.profession }}</p>
                        </div>
                        <div v-if="form.type_personnel === 'militaire'">
                            <label class="block text-sm font-medium text-slate-800 mb-1">Armée/Service</label>
                            <div class="flex gap-2">
                                <select v-model="form.armee_id"
                                    class="flex-1 rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500">
                                    <option value="">Sélectionner</option>
                                    <option v-for="a in armees" :key="a.id" :value="a.id">{{ a.nom }}</option>
                                    <option value="__nouveau__">➕ Ajouter une nouvelle...</option>
                                </select>
                            </div>
                            <input 
                                v-if="form.armee_id === '__nouveau__'"
                                v-model="nouvelleArmee"
                                type="text"
                                placeholder="Nom de la nouvelle armée/service"
                                class="mt-2 w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"
                            />
                            <p v-if="form.errors.armee_id" class="mt-1 text-sm text-red-500">{{ form.errors.armee_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Affectation (optionnelle) - Uniquement pour les militaires -->
                <div v-if="form.type_personnel === 'militaire'" class="border-b border-slate-200 pb-4">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Affectation <span class="text-xs font-normal text-slate-500">(optionnel)</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Catégorie</label>
                            <select v-model="selectedCategorie" @change="onCategorieChange"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500">
                                <option value="">Sélectionner une catégorie</option>
                                <option v-for="cat in categoriesGrades" :key="cat.id" :value="cat.id">{{ cat.libelle }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Grade</label>
                            <select v-model="form.grade_id" :disabled="!selectedCategorie"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500 disabled:opacity-50">
                                <option value="">Sélectionner un grade</option>
                                <option v-for="grade in filteredGrades" :key="grade.id" :value="grade.id">
                                    {{ grade.libelle }} ({{ grade.abreviation }})
                                </option>
                            </select>
                            <p v-if="form.errors.grade_id" class="mt-1 text-sm text-red-500">{{ form.errors.grade_id }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-slate-800 mb-1">Unité</label>
                        <input v-model="form.unite" type="text" placeholder="Ex: 1ère Compagnie, Bataillon XYZ..."
                            class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                        <p v-if="form.errors.unite" class="mt-1 text-sm text-red-500">{{ form.errors.unite }}</p>
                    </div>
                </div>

                <!-- Statut -->
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Statut</h3>
                    <div>
                        <label class="block text-sm font-medium text-slate-800 mb-1">Statut</label>
                        <select v-model="form.statut"
                            class="w-full rounded-lg border border-slate-300 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-slate-500">
                            <option value="En activité">En activité</option>
                            <option value="Non activite">Non activite</option>
                            <option value="En retraite">En retraite</option>
                            <option value="Radié">Radié</option>
                        </select>
                    </div>
                </div>

                <!-- Contact (optionnel) -->
                <div>
                    <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wide mb-3">Contact <span class="text-xs font-normal text-slate-500">(optionnel)</span></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Adresse</label>
                            <textarea v-model="form.adresse" rows="2" placeholder="Adresse personnelle"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-800 mb-1">Téléphone</label>
                            <input v-model="form.telephone" type="text" placeholder="+223 XX XX XX XX"
                                class="w-full px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <Link :href="route('militaires.index')"
                        class="px-4 py-2 border border-slate-300 text-slate-700 text-sm rounded-lg hover:bg-slate-50 transition-colors">
                        Annuler
                    </Link>
                    <button type="submit" :disabled="form.processing"
                        class="px-6 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors disabled:opacity-50 cursor-pointer">
                        <i v-if="form.processing" class="pi pi-spin pi-spinner mr-2"></i>
                        Créer le personnel
                    </button>
                </div>
            </form>
        </Card>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card } from '@/Components/GPJ';

const props = defineProps({
    categoriesGrades: Array,
    armees: Array,
    grades: Array,
    typePersonnelOptions: Array,
});

const selectedCategorie = ref('');
const nouvelleArmee = ref('');

const filteredGrades = computed(() => {
    if (!selectedCategorie.value) return [];
    const cat = props.categoriesGrades.find(c => c.id == selectedCategorie.value);
    return cat ? cat.grades : [];
});

const onCategorieChange = () => {
    form.grade_id = '';
};

const form = useForm({
    type_personnel: 'militaire',
    nom: '',
    prenoms: '',
    profession: '',
    matricule: '',
    date_naissance: '',
    lieu_naissance: '',
    nom_pere: '',
    prenoms_pere: '',
    nom_mere: '',
    prenoms_mere: '',
    grade_id: '',
    unite: '',
    adresse: '',
    telephone: '',
    statut: 'En activité',
    genre: '',
    armee_id: '',
});

// Watcher pour gérer la nouvelle armée
watch(nouvelleArmee, (val) => {
    if (val && val.trim()) {
        form.armee_id = val.trim();
    }
});

// Watcher pour réinitialiser les champs quand le type change
watch(() => form.type_personnel, (newVal) => {
    if (newVal === 'civil') {
        form.matricule = '';
        form.grade_id = '';
        form.armee_id = '';
        form.unite = '';
    }
});

const submit = () => {
    if (form.armee_id === '__nouveau__') {
        if (!nouvelleArmee.value || !nouvelleArmee.value.trim()) {
            form.errors.armee_id = 'Veuillez saisir le nom de la nouvelle armée/service';
            return;
        }
        form.armee_id = nouvelleArmee.value.trim();
    }
    form.post(route('militaires.store'));
};
</script>

<script>export default { layout: null };</script>