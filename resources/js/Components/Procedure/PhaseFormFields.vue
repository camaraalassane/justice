<template>
    <div class="space-y-4">
        <div v-if="champs && champs.length" class="border-t border-gpj-200 pt-4">
            <div class="flex items-center justify-between mb-3"><h4 class="text-xs font-semibold text-gpj-500 uppercase">Champs ({{ champs.length }})</h4></div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <div v-for="(champ, i) in champs" :key="'champ-'+i">
                    <div class="flex items-center justify-between mb-1"><label class="block text-xs font-medium text-gpj-600">{{ formatLabel(champ.cle) }}</label><button type="button" @click="supprimerChamp(i)" class="text-red-400 hover:text-red-600 text-xs"><i class="pi pi-times"></i></button></div>
                    <input v-if="champ.type === 'text'" v-model="champ.valeur" type="text" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <input v-else-if="champ.type === 'date'" v-model="champ.valeur" type="date" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <textarea v-else-if="champ.type === 'textarea'" v-model="champ.valeur" rows="2" class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
                </div>
            </div>
        </div>

        <div v-if="personnes && personnes.length" class="border-t border-gpj-200 pt-4">
            <div class="flex items-center justify-between mb-3"><h4 class="text-xs font-semibold text-gpj-500 uppercase">Personnes</h4><button type="button" @click="ajouterPersonne" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i> Ajouter</button></div>
            <div v-for="(p, i) in personnes" :key="'pers-'+i" class="p-3 bg-gpj-50 rounded-lg border border-gpj-100 mb-2">
                <div class="flex justify-between mb-2"><span class="text-xs text-gpj-500">Personne {{ i + 1 }}</span><button type="button" @click="personnes.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></div>
                <div class="grid grid-cols-2 gap-2">
                    <input v-model="p.nom" placeholder="Nom *" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <input v-model="p.prenom" placeholder="Prénom *" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <input v-model="p.profession" placeholder="Profession" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <input v-model="p.autre" placeholder="Autre" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                </div>
            </div>
        </div>

        <div v-if="evenements && evenements.length" class="border-t border-gpj-200 pt-4">
            <div class="flex items-center justify-between mb-3"><h4 class="text-xs font-semibold text-gpj-500 uppercase">Événements</h4><button type="button" @click="ajouterEvenement" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i> Ajouter</button></div>
            <div v-for="(e, i) in evenements" :key="'ev-'+i" class="p-3 bg-gpj-50 rounded-lg border border-gpj-100 mb-2">
                <div class="flex justify-between mb-2"><span class="text-xs text-gpj-500">Événement {{ i + 1 }}</span><button type="button" @click="evenements.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></div>
                <input v-model="e.nom" placeholder="Nom *" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                <input v-model="e.date_evenement" type="date" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                <textarea v-model="e.description" placeholder="Description" rows="2" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
            </div>
        </div>

        <div v-if="references && references.length" class="border-t border-gpj-200 pt-4">
            <div class="flex items-center justify-between mb-3"><h4 class="text-xs font-semibold text-gpj-500 uppercase">Références</h4><button type="button" @click="ajouterReference" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i> Ajouter</button></div>
            <div v-for="(r, i) in references" :key="'ref-'+i" class="p-3 bg-gpj-50 rounded-lg border border-gpj-100 mb-2">
                <div class="flex justify-between mb-2"><span class="text-xs text-gpj-500">Référence {{ i + 1 }}</span><button type="button" @click="references.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></div>
                <input v-model="r.libelle" placeholder="Libellé *" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                <textarea v-model="r.description" placeholder="Description" rows="2" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
            </div>
        </div>

        <div v-if="optionsCocher && optionsCocher.length" class="border-t border-gpj-200 pt-4">
            <h4 class="text-xs font-semibold text-gpj-500 uppercase mb-3">Options</h4>
            <div class="space-y-2">
                <label v-for="(o, i) in optionsCocher" :key="'opt-'+i" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gpj-50 cursor-pointer">
                    <input type="checkbox" v-model="o.est_coche" class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500" />
                    <span class="text-sm text-gpj-700 flex-1">{{ o.libelle }}</span>
                    <button type="button" @click="optionsCocher.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button>
                </label>
            </div>
        </div>

        <div v-if="piecesJointes && piecesJointes.length" class="border-t border-gpj-200 pt-4">
            <div class="flex items-center justify-between mb-3"><h4 class="text-xs font-semibold text-gpj-500 uppercase">Pièces jointes</h4><button type="button" @click="ajouterPieceJointe" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i> Ajouter</button></div>
            <div v-for="(pj, i) in piecesJointes" :key="'pj-'+i" class="p-3 bg-gpj-50 rounded-lg border border-gpj-100 mb-2">
                <div class="flex justify-between mb-2"><span class="text-xs text-gpj-500">Pièce {{ i + 1 }}</span><button type="button" @click="piecesJointes.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></div>
                <input v-model="pj.nom" placeholder="Nom *" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                <textarea v-model="pj.description" placeholder="Description" rows="2" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 mb-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea>
                <div class="mt-2">
                    <label class="block text-xs text-gpj-500 mb-1">Fichier PDF</label>
                    <input type="file" accept=".pdf" @change="(e) => onFileChange(e, i)" class="w-full text-xs text-gpj-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-gpj-100 file:text-gpj-600" />
                    <div v-if="pj.fichier" class="text-xs text-emerald-600 mt-1"><i class="pi pi-check mr-1"></i>{{ pj.fichier.name }}</div>
                </div>
            </div>
        </div>

        <div class="border-t border-gpj-200 pt-4 space-y-2">
            <button type="button" @click="showCustomForm = !showCustomForm" class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer"><i class="pi pi-plus-circle"></i> Ajouter un champ personnalisé</button>
            <div v-if="showCustomForm" class="p-4 bg-gpj-50 rounded-lg border border-gpj-100 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs text-gpj-500 mb-1">Nom <span class="text-red-500">*</span></label><input v-model="newField.cle" type="text" placeholder="Nom du champ" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" /></div>
                    <div><label class="block text-xs text-gpj-500 mb-1">Type</label><select v-model="newField.type" class="w-full rounded border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"><option value="text">Texte</option><option value="date">Date</option><option value="textarea">Texte long</option></select></div>
                </div>
                <div class="flex items-center gap-2"><button type="button" @click="ajouterNouveauChamp" class="px-3 py-1.5 bg-gpj-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 cursor-pointer">Ajouter</button><button type="button" @click="showCustomForm = false; resetNewField()" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button></div>
            </div>
            <button type="button" @click="ajouterOptionCocher" class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer"><i class="pi pi-plus-circle"></i> Ajouter une option à cocher</button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const props = defineProps({ phaseTypeId: [String, Number], phaseTypes: Array, champs: { type: Array, default: () => [] }, personnes: { type: Array, default: () => [] }, evenements: { type: Array, default: () => [] }, references: { type: Array, default: () => [] }, optionsCocher: { type: Array, default: () => [] }, piecesJointes: { type: Array, default: () => [] } });
const showCustomForm = ref(false); const newField = ref({ cle: '', type: 'text' });
const formatLabel = (c) => (c || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
const resetNewField = () => { newField.value = { cle: '', type: 'text' }; };
const ajouterNouveauChamp = () => { if (!newField.value.cle.trim()) return alert('Nom requis.'); props.champs.push({ cle: newField.value.cle.trim().toLowerCase().replace(/\s+/g, '_'), valeur: '', type: newField.value.type, _custom: true }); showCustomForm.value = false; resetNewField(); };
const supprimerChamp = (i) => props.champs.splice(i, 1);
const ajouterOptionCocher = () => { const l = prompt('Libellé :'); if (l?.trim()) props.optionsCocher.push({ libelle: l.trim(), est_coche: false, _custom: true }); };
const ajouterPersonne = () => props.personnes.push({ nom: '', prenom: '', profession: '', autre: '' });
const ajouterEvenement = () => props.evenements.push({ nom: '', date_evenement: '', description: '' });
const ajouterReference = () => props.references.push({ libelle: '', description: '' });
const ajouterPieceJointe = () => props.piecesJointes.push({ nom: '', description: '', contexte: '' });
const onFileChange = (e, i) => { const f = e.target.files[0]; if (f && props.piecesJointes?.[i]) props.piecesJointes[i].fichier = f; };
</script>