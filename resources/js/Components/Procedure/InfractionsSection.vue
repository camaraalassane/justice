<template>
    <div class="space-y-6">
        <!-- Infractions (avec recherche) -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide">
                    <i class="pi pi-list mr-2"></i> Infractions
                    <span v-if="!hasFautes" class="text-red-500">*</span>
                </h3>
                <button type="button" @click="showQuickCreate = !showQuickCreate"
                    class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-plus-circle"></i> Nouvelle infraction
                </button>
            </div>

            <!-- Création rapide d'infraction -->
            <div v-if="showQuickCreate" class="p-4 bg-gpj-50 rounded-lg border border-gpj-100 mb-3">
                <h4 class="text-xs font-medium text-gpj-600 mb-3">Ajouter une infraction à la nomenclature</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                    <div>
                        <label class="block text-xs text-gpj-500 mb-1">Libellé <span class="text-red-500">*</span></label>
                        <input v-model="quickForm.libelle" type="text" required placeholder="Ex: Désertion..."
                            class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    </div>
                    <div>
                        <label class="block text-xs text-gpj-500 mb-1">Classification <span class="text-red-500">*</span></label>
                        <select v-model="quickForm.classification" required
                            class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Choisir</option>
                            <option value="Criminelle">Criminelle</option>
                            <option value="Délictuelle">Délictuelle</option>
                            <option value="Contravention">Contravention</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gpj-500 mb-1">Nature</label>
                        <select v-model="quickForm.nature"
                            class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                            <option value="">Choisir</option>
                            <option>Atteinte à l'honneur</option>
                            <option>Atteinte aux biens</option>
                            <option>Manquement à la discipline</option>
                            <option>Infraction au droit commun</option>
                            <option>Désertion</option>
                            <option>Trahison</option>
                        </select>
                    </div>
                </div>
                <div v-if="quickError" class="text-xs text-red-500 mb-2">{{ quickError }}</div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="createInfraction" :disabled="quickCreating"
                        class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors disabled:opacity-50 cursor-pointer">
                        <i v-if="quickCreating" class="pi pi-spin pi-spinner mr-1"></i>Créer et ajouter
                    </button>
                    <button type="button" @click="showQuickCreate = false; quickError = ''"
                        class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button>
                </div>
            </div>

            <!-- Recherche et sélection -->
            <div class="relative mb-3">
                <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gpj-400 text-sm"></i>
                <input v-model="searchInfraction" type="text" placeholder="Rechercher une infraction..."
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gpj-200 text-sm focus:outline-none focus:ring-2 focus:ring-gpj-500" />
            </div>

            <div class="space-y-2 max-h-60 overflow-y-auto border border-gpj-200 rounded-lg p-3">
                <label v-for="infraction in filteredInfractions" :key="infraction.id"
                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gpj-50 cursor-pointer">
                    <input type="checkbox" :value="infraction.id" v-model="selectedInfractions"
                        class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500" />
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gpj-800">{{ infraction.libelle }}</p>
                        <p class="text-xs text-gpj-400">{{ infraction.classification }}</p>
                    </div>
                    <Badge variant="neutral" size="sm">{{ infraction.code_infraction }}</Badge>
                </label>
                <p v-if="!filteredInfractions.length" class="text-sm text-gpj-400 text-center py-4">
                    Aucune infraction trouvée
                </p>
            </div>
            <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
        </div>

        <!-- Fautes militaires -->
        <div class="border-t border-gpj-100 pt-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gpj-500 uppercase tracking-wide">
                    <i class="pi pi-exclamation-triangle mr-2"></i> Fautes militaires
                </h3>
                <button type="button" @click="ajouterFaute"
                    class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer">
                    <i class="pi pi-plus-circle"></i> Ajouter une faute
                </button>
            </div>
            <div v-for="(faute, i) in fautesMilitaires" :key="i"
                class="p-3 bg-gpj-50 rounded-lg border border-gpj-100 mb-2">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gpj-500">Faute {{ i + 1 }}</span>
                    <button type="button" @click="fautesMilitaires.splice(i, 1)"
                        class="text-red-400 hover:text-red-600 text-xs cursor-pointer">
                        <i class="pi pi-times-circle"></i> Supprimer
                    </button>
                </div>
                <div class="space-y-2">
                    <input v-model="faute.libelle" type="text" required placeholder="Libellé de la faute *"
                        class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <textarea v-model="faute.description" rows="2" placeholder="Description"
                        class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Badge from '@/Components/GPJ/Badge.vue';

const props = defineProps({
    infractions: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
    fautes: { type: Array, default: () => [] },
    error: String,
});

const emit = defineEmits(['update:modelValue', 'update:fautes', 'infraction-created']);

const searchInfraction = ref('');
const showQuickCreate = ref(false);
const quickCreating = ref(false);
const quickError = ref('');

const quickForm = ref({
    libelle: '',
    classification: '',
    nature: '',
});

const selectedInfractions = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});

const fautesMilitaires = computed({
    get: () => props.fautes,
    set: (val) => emit('update:fautes', val),
});

const hasFautes = computed(() => {
    return props.fautes.some(f => f.libelle && f.libelle.trim() !== '');
});

const filteredInfractions = computed(() => {
    if (!searchInfraction.value.trim()) return props.infractions;
    const q = searchInfraction.value.toLowerCase().trim();
    return props.infractions.filter(i =>
        i.libelle.toLowerCase().includes(q) ||
        i.code_infraction.toLowerCase().includes(q) ||
        i.classification.toLowerCase().includes(q)
    );
});

const createInfraction = async () => {
    if (!quickForm.value.libelle || !quickForm.value.classification) {
        quickError.value = 'Libellé et classification requis.';
        return;
    }
    quickCreating.value = true;
    quickError.value = '';

    try {
        // Récupérer le token CSRF depuis la page
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        const r = await fetch('/api/infractions/quick-create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(quickForm.value),
        });

        const data = await r.json();

        if (!r.ok) {
            quickError.value = data.message || 'Erreur lors de la création.';
            if (data.errors) {
                const msgs = Object.values(data.errors).flat();
                quickError.value = msgs.join(', ');
            }
            return;
        }

        if (data.id) {
            emit('infraction-created', data);
            selectedInfractions.value = [...selectedInfractions.value, data.id];
            quickForm.value = { libelle: '', classification: '', nature: '' };
            showQuickCreate.value = false;
        }
    } catch (e) {
        console.error('Erreur réseau:', e);
        quickError.value = 'Erreur réseau. Vérifiez votre connexion.';
    }
    quickCreating.value = false;
};

const ajouterFaute = () => {
    fautesMilitaires.value = [...fautesMilitaires.value, { libelle: '', description: '' }];
};
</script>