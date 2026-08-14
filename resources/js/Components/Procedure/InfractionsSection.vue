<template>
    <div class="space-y-4">
        <!-- En-tête -->
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-medium text-gpj-700">Infractions</h4>
                <p class="text-xs text-gpj-400">Sélectionnez les infractions pour ce militaire</p>
            </div>
            <span class="text-xs text-gpj-500 bg-slate-100 px-2 py-0.5 rounded-full">
                {{ selectedInfractions.length }} sélectionnée(s)
            </span>
        </div>

        <!-- Liste des infractions avec recherche -->
        <div class="space-y-2">
            <!-- Barre de recherche + bouton créer -->
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <i class="pi pi-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gpj-400 text-xs"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher une infraction..."
                        class="w-full pl-8 pr-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    />
                </div>
                <button
                    v-if="peutCreer"
                    @click="ouvrirModalCreation"
                    class="px-3 py-1.5 bg-slate-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-1 whitespace-nowrap"
                >
                    <i class="pi pi-plus text-xs"></i> Créer
                </button>
            </div>

            <!-- Liste des infractions filtrées -->
            <div class="max-h-60 overflow-y-auto space-y-1">
                <div
                    v-for="inf in infractionsFiltrees"
                    :key="inf.id"
                    class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded-lg cursor-pointer transition-colors"
                    @click="toggleInfraction(inf.id)"
                >
                    <input
                        type="checkbox"
                        :checked="selectedInfractions.includes(inf.id)"
                        @click.stop
                        @change="toggleInfraction(inf.id)"
                        class="rounded border-slate-400 text-gpj-500 focus:ring-gpj-500 shrink-0"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gpj-700 truncate">{{ inf.libelle }}</p>
                        <div class="flex items-center gap-1 flex-wrap">
                            <span class="text-[10px] text-gpj-400">{{ inf.code_infraction }}</span>
                            <Badge v-if="inf.classification" variant="neutral" size="sm" class="text-[9px]">
                                {{ inf.classification }}
                            </Badge>
                            <Badge v-if="inf.nature" variant="default" size="sm" class="text-[9px]">
                                {{ inf.nature }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div v-if="infractionsFiltrees.length === 0" class="text-center py-4 text-gpj-400 text-xs">
                    <i class="pi pi-inbox text-lg block mb-1"></i>
                    <span v-if="searchQuery">Aucune infraction trouvée pour "{{ searchQuery }}"</span>
                    <span v-else>Aucune infraction disponible</span>
                </div>
            </div>
        </div>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-xs text-red-500">{{ error }}</p>

        <!-- Modale de création d'infraction -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gpj-800 dark:text-white">Créer une infraction</h3>
                    <button @click="fermerModalCreation" class="text-gpj-400 hover:text-gpj-600">
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <form @submit.prevent="creerInfraction" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Libellé <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="newInfraction.libelle"
                                type="text"
                                required
                                placeholder="Ex: Insoumission"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Code <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="newInfraction.code_infraction"
                                type="text"
                                required
                                placeholder="Ex: INF-001"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Classification <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="newInfraction.classification"
                                required
                                class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            >
                                <option value="">Sélectionner</option>
                                <option value="Criminelle">Criminelle</option>
                                <option value="Délictuelle">Délictuelle</option>
                                <option value="Contravention">Contravention</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gpj-700 mb-1">
                                Nature
                            </label>
                            <input
                                v-model="newInfraction.nature"
                                type="text"
                                placeholder="Ex: Infraction militaire"
                                class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">
                            Description
                        </label>
                        <textarea
                            v-model="newInfraction.description"
                            rows="3"
                            placeholder="Description détaillée de l'infraction..."
                            class="w-full rounded-lg border border-slate-300 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center gap-3 pt-3 border-t border-slate-200">
                        <button
                            type="button"
                            @click="fermerModalCreation"
                            class="px-4 py-2 border border-slate-300 text-gpj-600 text-sm rounded-lg hover:bg-slate-50 transition-colors"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            :disabled="creerEnCours"
                            class="flex-1 px-4 py-2 bg-slate-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 disabled:opacity-50 transition-colors"
                        >
                            <i v-if="creerEnCours" class="pi pi-spin pi-spinner mr-2"></i>
                            Créer l'infraction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Badge } from '@/Components/GPJ';

const props = defineProps({
    infractions: {
        type: Array,
        required: true,
        default: () => []
    },
    modelValue: {
        type: Array,
        default: () => []
    },
    fautes: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: null
    },
    peutCreer: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['update:modelValue', 'update:fautes', 'infraction-created']);

// ====== ÉTATS ======
const searchQuery = ref('');
const showCreateModal = ref(false);
const creerEnCours = ref(false);
const newInfraction = ref({
    libelle: '',
    code_infraction: '',
    classification: '',
    nature: '',
    description: ''
});

const selectedInfractions = ref([...props.modelValue]);

// ====== COMPUTED ======
const infractionsFiltrees = computed(() => {
    if (!searchQuery.value) return props.infractions;
    const query = searchQuery.value.toLowerCase().trim();
    return props.infractions.filter(inf => 
        inf.libelle?.toLowerCase().includes(query) ||
        inf.code_infraction?.toLowerCase().includes(query) ||
        inf.classification?.toLowerCase().includes(query) ||
        inf.nature?.toLowerCase().includes(query)
    );
});

// ====== MÉTHODES ======
const toggleInfraction = (id) => {
    const index = selectedInfractions.value.indexOf(id);
    if (index === -1) {
        selectedInfractions.value.push(id);
    } else {
        selectedInfractions.value.splice(index, 1);
    }
    emit('update:modelValue', selectedInfractions.value);
};

const ouvrirModalCreation = () => {
    newInfraction.value = {
        libelle: '',
        code_infraction: '',
        classification: '',
        nature: '',
        description: ''
    };
    showCreateModal.value = true;
};

const fermerModalCreation = () => {
    showCreateModal.value = false;
    newInfraction.value = {
        libelle: '',
        code_infraction: '',
        classification: '',
        nature: '',
        description: ''
    };
};

const creerInfraction = async () => {
    if (!newInfraction.value.libelle || !newInfraction.value.code_infraction || !newInfraction.value.classification) {
        return;
    }

    creerEnCours.value = true;

    try {
        const response = await fetch('/api/infractions/quick-create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(newInfraction.value)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Erreur lors de la création');
        }

        const data = await response.json();
        
        // Émettre l'événement avec la nouvelle infraction
        emit('infraction-created', data);
        
        // Sélectionner automatiquement la nouvelle infraction
        selectedInfractions.value.push(data.id);
        emit('update:modelValue', selectedInfractions.value);
        
        // Fermer la modale
        fermerModalCreation();

    } catch (error) {
        console.error('Erreur création infraction:', error);
        alert('Erreur lors de la création : ' + error.message);
    } finally {
        creerEnCours.value = false;
    }
};

// ====== WATCH ======
watch(() => props.modelValue, (newVal) => {
    selectedInfractions.value = [...newVal];
}, { deep: true });
</script>