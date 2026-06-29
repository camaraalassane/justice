<template>
    <AuthenticatedLayout title="Détail Procédure" :subtitle="procedure.numero_procedure">
        <!-- Messages flash -->
        <div v-if="flashError" class="fixed top-4 right-4 z-50 max-w-md bg-red-500 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <i class="pi pi-exclamation-triangle text-lg mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold">Erreur</p>
                    <p class="text-sm whitespace-pre-wrap">{{ flashError }}</p>
                </div>
                <button @click="flashError = null" class="text-white hover:opacity-80">
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>

        <div v-if="flashSuccess" class="fixed top-4 right-4 z-50 max-w-md bg-emerald-500 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-start gap-3">
                <i class="pi pi-check-circle text-lg mt-0.5"></i>
                <div class="flex-1">
                    <p class="font-semibold">Succès</p>
                    <p class="text-sm">{{ flashSuccess }}</p>
                </div>
                <button @click="flashSuccess = null" class="text-white hover:opacity-80">
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>

        <div v-if="flashMessage" :class="['fixed top-4 right-4 z-50 px-5 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 max-w-sm', flashMessage.type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white']">
            <i :class="flashMessage.type === 'success' ? 'pi pi-check-circle' : 'pi pi-exclamation-circle'"></i>
            {{ flashMessage.message }}
            <button @click="flashMessage = null" class="ml-2 hover:opacity-80"><i class="pi pi-times text-xs"></i></button>
        </div>

        <div class="space-y-6">
            <!-- Ajouter une phase -->
            <Card v-if="peutValider && phasesDisponibles.length > 0">
                <template #header><div class="px-6 py-4 border-b border-gpj-200"><h3 class="text-lg font-semibold text-gpj-800">Ajouter une phase</h3></div></template>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><label class="block text-sm font-medium text-gpj-700 mb-1">Type de phase <span class="text-red-500">*</span></label><select v-model="phaseForm.phase_type_id" required @change="onPhaseTypeChange" class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"><option value="">Choisir</option><option v-for="pt in phasesDisponibles" :key="pt.id" :value="pt.id">{{ pt.libelle }}</option><option value="autre">Autre (personnalisé)</option></select></div>
                        <div v-if="phaseForm.phase_type_id === 'autre'"><label class="block text-sm font-medium text-gpj-700 mb-1">Nom <span class="text-red-500">*</span></label><input v-model="phaseForm.phase_personnalisee" type="text" required class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" /></div>
                        <div><label class="block text-sm font-medium text-gpj-700 mb-1">Date <span class="text-red-500">*</span></label><input v-model="phaseForm.date_phase" type="date" required class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" /></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gpj-700 mb-1">Description</label><textarea v-model="phaseForm.description" rows="2" class="w-full rounded-lg border border-gpj-200 text-sm py-2.5 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"></textarea></div>
                    <PhaseFormFields :phaseTypeId="phaseForm.phase_type_id" :phaseTypes="phaseTypes" v-model:champs="phaseForm.champs" v-model:personnes="phaseForm.personnes" v-model:evenements="phaseForm.evenements" v-model:references="phaseForm.references" v-model:optionsCocher="phaseForm.options_cocher" v-model:piecesJointes="phaseForm.pieces_jointes" />
                    <div class="flex items-center gap-3"><button type="button" @click="ajouterPhase" :disabled="formProcessing" class="px-6 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 disabled:opacity-50 cursor-pointer"><i v-if="formProcessing" class="pi pi-spin pi-spinner mr-2"></i>Ajouter cette phase</button></div>
                </div>
            </Card>

            <div v-if="peutValider && phasesDisponibles.length === 0" class="p-4 bg-gpj-50 border border-gpj-200 rounded-lg text-sm text-gpj-600"><i class="pi pi-info-circle mr-2"></i> Toutes les phases ont été effectuées.</div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Infos générales -->
                    <Card title="Informations Générales">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div><p class="text-gpj-400">N° Procédure</p><p class="font-medium">{{ procedure.numero_procedure }}</p></div>
                            <div>
                                <p class="text-gpj-400">Phase actuelle</p>
                                <div class="flex items-center gap-2">
                                    <Badge variant="info" size="sm">{{ procedure.phase || '-' }}</Badge>
                                    <span v-if="procedure.est_plurielle" class="text-[10px] bg-purple-100 text-purple-600 px-2 py-0.5 rounded-full">Pluriel</span>
                                </div>
                            </div>
                            <div><p class="text-gpj-400">Parquet</p><div v-if="editParquet" class="flex items-center gap-2"><select v-model="editForm.parquet_competent" class="rounded-lg border border-gpj-200 text-sm py-1.5 px-2"><option v-for="p in parquets" :key="p" :value="p">{{ p }}</option></select><button @click="saveParquet" class="text-emerald-500"><i class="pi pi-check text-sm"></i></button><button @click="editParquet = false" class="text-red-400"><i class="pi pi-times text-sm"></i></button></div><div v-else class="flex items-center gap-2"><Badge variant="info" size="sm">{{ procedure.parquet_competent || 'Non défini' }}</Badge><button v-if="peutValider" @click="editParquet = true; editForm.parquet_competent = procedure.parquet_competent" class="text-gpj-400"><i class="pi pi-pencil text-xs"></i></button></div></div>
                            <div><p class="text-gpj-400">Date ouverture</p><div v-if="editDateOuverture" class="flex items-center gap-2"><input v-model="editForm.date_ouverture" type="date" class="rounded-lg border border-gpj-200 text-sm py-1.5 px-2" /><button @click="saveDateOuverture" class="text-emerald-500"><i class="pi pi-check text-sm"></i></button><button @click="editDateOuverture = false" class="text-red-400"><i class="pi pi-times text-sm"></i></button></div><div v-else class="flex items-center gap-2"><p class="font-medium">{{ formatDate(procedure.date_ouverture) }}</p><button v-if="peutValider" @click="editDateOuverture = true; editForm.date_ouverture = formatDateForInput(procedure.date_ouverture)" class="text-gpj-400"><i class="pi pi-pencil text-xs"></i></button></div></div>
                            <div><p class="text-gpj-400">Créé par</p><p class="font-medium">{{ procedure.createur?.name || '-' }}</p></div>
                            <div><p class="text-gpj-400">Validé par</p><p class="font-medium">{{ procedure.validateur?.name || '-' }}</p></div>
                        </div>
                    </Card>

                    <!-- Militaires concernés -->
                    <Card :title="procedure.est_plurielle ? 'Militaires concernés' : 'Militaire concerné'">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gpj-400">
                                {{ procedure.procedure_militaires?.length || 0 }} militaire(s) associé(s)
                            </span>
                            <button
                                v-if="peutValider"
                                @click="openAddMilitaireModal"
                                class="px-3 py-1.5 bg-gpj-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-1"
                            >
                                <i class="pi pi-plus"></i> Ajouter un militaire
                            </button>
                        </div>

                        <div v-if="procedure.procedure_militaires?.length" class="space-y-4">
                            <MilitaireDetail
                                v-for="(pm, index) in procedure.procedure_militaires"
                                :key="pm.id"
                                :procedure-militaire="pm"
                                :procedure-id="procedure.id"
                                :est-principal="pm.militaire_id === procedure.militaire_id"
                                :all-infractions="allInfractions"
                                :peut-modifier="peutValider"
                                :grades="grades"
                                :armees="armees"
                                @updated="rechargerProcedure"
                            />
                        </div>
                        <p v-else class="text-sm text-gpj-400 py-4 text-center">Aucun militaire associé</p>
                    </Card>

                    <!-- Historique des phases -->
                    <Card title="Historique des Phases">
                        <div v-if="procedure.procedure_phases?.length" class="space-y-4">
                            <div v-for="(phase, index) in procedure.procedure_phases" :key="phase.id" class="flex gap-3">
                                <div class="flex flex-col items-center"><div :class="['w-3 h-3 rounded-full mt-1.5', phase.est_retour ? 'bg-amber-400' : 'bg-gpj-500']"></div><div v-if="index < procedure.procedure_phases.length - 1" class="w-0.5 flex-1 bg-gpj-200"></div></div>
                                <div class="flex-1 pb-4 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <div class="flex-1 min-w-0"><div class="flex items-center gap-2"><span class="text-sm font-bold text-gpj-800">{{ phase.libelle || phase.phase_type?.libelle || 'Phase sans nom' }}</span><Badge v-if="index === 0" variant="info" size="sm">Actuelle</Badge><Badge v-if="index === procedure.procedure_phases.length - 1" variant="success" size="sm">Initiale</Badge></div><p class="text-xs text-gpj-400 mt-0.5">{{ formatDate(phase.date_phase) }} — par {{ phase.createur?.name || '-' }}</p></div>
                                        <button v-if="peutValider && editingPhaseId !== phase.id" @click="startEditPhase(phase)" class="text-gpj-400 hover:text-gpj-600 shrink-0" title="Modifier"><i class="pi pi-pencil text-xs"></i></button>
                                        <button v-if="peutValider && index === 0 && procedure.procedure_phases.length > 1 && editingPhaseId !== phase.id" @click="confirmRetourPhase(phase)" class="text-amber-500 hover:text-amber-700 shrink-0" title="Revenir à la phase précédente"><i class="pi pi-undo text-xs"></i></button>
                                    </div>
                                    <div v-if="editingPhaseId === phase.id" class="mt-3 p-3 bg-gpj-50 rounded-lg border border-gpj-200 space-y-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><div><label class="block text-xs font-medium text-gpj-500 mb-1">Description</label><textarea v-model="editPhaseForm.description" rows="2" class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2"></textarea></div><div><label class="block text-xs font-medium text-gpj-500 mb-1">Date</label><input v-model="editPhaseForm.date_phase" type="date" class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2" /></div></div>
                                        <div v-if="editPhaseForm.champs?.length" class="border-t border-gpj-100 pt-3"><p class="text-xs font-medium text-gpj-500 mb-2">Champs ({{ editPhaseForm.champs.length }})</p><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"><div v-for="(ch, i) in editPhaseForm.champs" :key="'ech-'+i"><div class="flex items-center justify-between mb-1"><label class="block text-xs text-gpj-400">{{ formatLabel(ch.cle) }}</label><button type="button" @click="editPhaseForm.champs.splice(i, 1)" class="text-red-400 hover:text-red-600 text-xs"><i class="pi pi-times"></i></button></div><input v-if="ch.type === 'text'" v-model="ch.valeur" type="text" class="w-full rounded border border-gpj-200 text-sm py-1 px-2" /><input v-else-if="ch.type === 'date'" v-model="ch.valeur" type="date" class="w-full rounded border border-gpj-200 text-sm py-1 px-2" /><div v-if="ch.type === 'date' && ch.valeur" class="text-xs text-gpj-400 mt-0.5">📅 {{ formatDate(ch.valeur) }}</div><textarea v-else-if="ch.type === 'textarea'" v-model="ch.valeur" rows="2" class="w-full rounded border border-gpj-200 text-sm py-1 px-2"></textarea></div></div></div>
                                        <div v-if="editPhaseForm.personnes?.length" class="border-t border-gpj-100 pt-3"><div class="flex items-center justify-between mb-2"><p class="text-xs font-medium text-gpj-500">Personnes ({{ editPhaseForm.personnes.length }})</p><button type="button" @click="editPhaseForm.personnes.push({ nom: '', prenom: '', profession: '', autre: '' })" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i></button></div><div v-for="(p, i) in editPhaseForm.personnes" :key="'ep-'+i" class="grid grid-cols-2 gap-1 mb-2 p-2 bg-white rounded border border-gpj-100"><input v-model="p.nom" placeholder="Nom" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5" /><input v-model="p.prenom" placeholder="Prénom" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5" /><input v-model="p.profession" placeholder="Profession" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5" /><input v-model="p.autre" placeholder="Autre" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5" /></div></div>
                                        <div v-if="editPhaseForm.evenements?.length" class="border-t border-gpj-100 pt-3"><div class="flex items-center justify-between mb-2"><p class="text-xs font-medium text-gpj-500">Événements ({{ editPhaseForm.evenements.length }})</p><button type="button" @click="editPhaseForm.evenements.push({ nom: '', date_evenement: '', description: '' })" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i></button></div><div v-for="(e, i) in editPhaseForm.evenements" :key="'ee-'+i" class="mb-2 p-2 bg-white rounded border border-gpj-100"><input v-model="e.nom" placeholder="Nom" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5 mb-1" /><input v-model="e.date_evenement" type="date" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5 mb-1" /><div v-if="e.date_evenement" class="text-xs text-gpj-400 mb-1">📅 {{ formatDate(e.date_evenement) }}</div><textarea v-model="e.description" placeholder="Description" rows="1" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5"></textarea></div></div>
                                        <div v-if="editPhaseForm.references?.length" class="border-t border-gpj-100 pt-3"><div class="flex items-center justify-between mb-2"><p class="text-xs font-medium text-gpj-500">Références ({{ editPhaseForm.references.length }})</p><button type="button" @click="editPhaseForm.references.push({ libelle: '', description: '' })" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i></button></div><div v-for="(r, i) in editPhaseForm.references" :key="'er-'+i" class="mb-2 p-2 bg-white rounded border border-gpj-100"><input v-model="r.libelle" placeholder="Libellé" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5 mb-1" /><textarea v-model="r.description" placeholder="Description" rows="1" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5"></textarea></div></div>
                                        <div v-if="editPhaseForm.options_cocher?.length" class="border-t border-gpj-100 pt-3"><div class="flex items-center justify-between mb-2"><p class="text-xs font-medium text-gpj-500">Options ({{ editPhaseForm.options_cocher.length }})</p><button type="button" @click="editPhaseForm.options_cocher.push({ libelle: 'Nouvelle option', est_coche: false, _custom: true })" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i></button></div><label v-for="(o, i) in editPhaseForm.options_cocher" :key="'eo-'+i" class="flex items-center gap-2 py-1 cursor-pointer"><input type="checkbox" v-model="o.est_coche" class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500 shrink-0" /><input v-if="o._custom" v-model="o.libelle" type="text" class="flex-1 text-xs border-b border-gpj-200 py-0.5" /><span v-else class="text-xs text-gpj-700 flex-1">{{ o.libelle }}</span><button type="button" @click="editPhaseForm.options_cocher.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></label></div>
                                        <div v-if="editPhaseForm.pieces_jointes?.length" class="border-t border-gpj-100 pt-3"><div class="flex items-center justify-between mb-2"><p class="text-xs font-medium text-gpj-500">Pièces jointes ({{ editPhaseForm.pieces_jointes.length }})</p><button type="button" @click="editPhaseForm.pieces_jointes.push({ nom: '', description: '' })" class="text-xs text-gpj-500"><i class="pi pi-plus-circle"></i></button></div><div v-for="(pj, i) in editPhaseForm.pieces_jointes" :key="'epj-'+i" class="mb-2 p-2 bg-white rounded border border-gpj-100"><div class="flex justify-between mb-1"><span class="text-xs text-gpj-500">Pièce {{ i + 1 }}</span><button type="button" @click="editPhaseForm.pieces_jointes.splice(i, 1)" class="text-red-400 text-xs"><i class="pi pi-times"></i></button></div><input v-model="pj.nom" placeholder="Nom" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5 mb-1" /><textarea v-model="pj.description" placeholder="Description" rows="1" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5 mb-1"></textarea><div v-if="pj.chemin_fichier && !pj.fichier" class="text-xs text-gpj-500 mb-1 flex items-center gap-2"><i class="pi pi-file-pdf text-red-500"></i><a :href="'/storage/' + pj.chemin_fichier" target="_blank" class="text-gpj-500 hover:underline">Voir le PDF</a><button type="button" @click="pj.chemin_fichier = null; pj._supprimerFichier = true" class="text-red-400 hover:text-red-600"><i class="pi pi-trash text-xs"></i></button></div><input type="file" accept=".pdf" @change="(e) => onEditFileChange(e, i)" class="w-full text-xs text-gpj-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-gpj-100 file:text-gpj-600" /><div v-if="pj.fichier" class="text-xs text-emerald-600 mt-1"><i class="pi pi-check mr-1"></i>{{ pj.fichier.name }}</div></div></div>
                                        <div class="border-t border-gpj-100 pt-3"><button type="button" @click="showEditCustomField = !showEditCustomField" class="text-xs text-gpj-500 hover:text-gpj-700 font-medium flex items-center gap-1 cursor-pointer"><i class="pi pi-plus-circle"></i> Ajouter un champ personnalisé</button><div v-if="showEditCustomField" class="mt-2 p-3 bg-white rounded border border-gpj-100 space-y-2"><div class="grid grid-cols-2 gap-2"><input v-model="editCustomField.cle" type="text" placeholder="Nom du champ *" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5" /><select v-model="editCustomField.type" class="w-full rounded border border-gpj-200 text-xs py-1 px-1.5"><option value="text">Texte</option><option value="date">Date</option><option value="textarea">Texte long</option></select></div><div class="flex items-center gap-2"><button type="button" @click="addCustomFieldToEdit" class="px-2 py-1 bg-gpj-500 text-white text-xs rounded hover:bg-gpj-600 cursor-pointer">Ajouter</button><button type="button" @click="showEditCustomField = false" class="text-xs text-gpj-400 hover:text-gpj-600 cursor-pointer">Annuler</button></div></div></div>
                                        <div class="flex items-center gap-2 pt-2 border-t border-gpj-100"><button @click="savePhaseEdit(phase.id)" :disabled="editPhaseProcessing" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer"><i v-if="editPhaseProcessing" class="pi pi-spin pi-spinner mr-1"></i>Enregistrer</button><button @click="cancelEditPhase" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button></div>
                                    </div>
                                    <div v-else>
                                        <p v-if="phase.description" class="text-xs text-gpj-500 mt-1">{{ phase.description }}</p>
                                        <div v-if="phase.champs?.length" class="mt-2"><p class="text-xs font-medium text-gpj-500 mb-1">Champs ({{ phase.champs.length }})</p><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"><div v-for="ch in phase.champs" :key="ch.id" class="bg-white rounded border border-gpj-100 p-2"><span class="text-xs text-gpj-400">{{ formatLabel(ch.cle) }}:</span><span class="text-xs text-gpj-700 ml-1">{{ ch.type === 'date' ? formatDate(ch.valeur) : (ch.valeur || '-') }}</span></div></div></div>
                                        <div v-if="phase.personnes?.length" class="mt-2"><p class="text-xs font-medium text-gpj-500">Personnes :</p><div v-for="p in phase.personnes" :key="p.id" class="text-xs text-gpj-600 ml-2">{{ p.nom }} {{ p.prenom }}{{ p.profession ? ' - ' + p.profession : '' }}</div></div>
                                        <div v-if="phase.evenements?.length" class="mt-2"><p class="text-xs font-medium text-gpj-500">Événements :</p><div v-for="e in phase.evenements" :key="e.id" class="text-xs text-gpj-600 ml-2">{{ e.nom }}{{ e.date_evenement ? ' (' + formatDate(e.date_evenement) + ')' : '' }}</div></div>
                                        <div v-if="phase.references?.length" class="mt-2"><p class="text-xs font-medium text-gpj-500">Références :</p><div v-for="r in phase.references" :key="r.id" class="text-xs text-gpj-600 ml-2">{{ r.libelle }}</div></div>
                                        <div v-if="phase.options_cocher?.filter(o => o.est_coche).length" class="mt-2 flex flex-wrap gap-1"><Badge v-for="o in phase.options_cocher.filter(o => o.est_coche)" :key="o.id" variant="success" size="sm">{{ o.libelle }}</Badge></div>
                                        <div v-if="phase.pieces_jointes?.length" class="mt-2"><p class="text-xs font-medium text-gpj-500">Pièces jointes :</p><div v-for="pj in phase.pieces_jointes" :key="pj.id" class="text-xs text-gpj-600 ml-2 flex items-center gap-2"><i class="pi pi-file-pdf text-red-500"></i><span>{{ pj.nom }}</span><a v-if="pj.chemin_fichier" :href="'/storage/' + pj.chemin_fichier" target="_blank" class="text-gpj-500 hover:underline"><i class="pi pi-external-link text-xs"></i> Voir</a></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gpj-400 text-sm py-4">Aucune phase enregistrée</p>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Bouton Export PDF -->
                    <a :href="route('procedures.export-pdf', procedure.id)" target="_blank" class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 transition-colors">
                        <i class="pi pi-download"></i> Exporter en PDF
                    </a>

                    <Card v-if="procedure.jugement" title="Jugement">
                        <div class="space-y-2 text-sm">
                            <div><p class="text-gpj-400">Date</p><p class="font-medium">{{ formatDate(procedure.jugement.date_jugement) }}</p></div>
                            <div><p class="text-gpj-400">N° Jugement</p><p class="font-medium">{{ procedure.jugement.numero_jugement }}</p></div>
                            <div><p class="text-gpj-400">Juridiction</p><p class="font-medium">{{ procedure.jugement.juridiction }}</p></div>
                            <div><p class="text-gpj-400">Verdict</p><Badge :variant="procedure.jugement.verdict === 'Condamnation' ? 'danger' : 'success'">{{ procedure.jugement.verdict }}</Badge></div>
                            <div v-if="procedure.jugement.peine_principale"><p class="text-gpj-400">Peine</p><p class="font-medium">{{ procedure.jugement.peine_principale }}</p></div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Modale Ajouter un militaire -->
        <div v-if="showAddMilitaireModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Ajouter un militaire à la procédure</h3>
                
                <div class="space-y-4">
                    <!-- Recherche/sélection du militaire -->
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Rechercher un militaire existant</label>
                        <SearchSelect
                            :options="optionsMilitaires"
                            v-model="newMilitaire.militaire_id"
                            placeholder="Rechercher par nom, matricule..."
                            @search="rechercherMilitaires"
                            @change="onMilitaireChange"
                        />
                    </div>

                    <div class="border-t border-gpj-200 pt-4">
                        <p class="text-sm font-medium text-gpj-700 mb-2">OU créer un nouveau militaire</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gpj-600 mb-1">Nom *</label>
                                <input v-model="newMilitaire.nom" type="text" placeholder="Nom" class="w-full rounded border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gpj-600 mb-1">Prénom *</label>
                                <input v-model="newMilitaire.prenom" type="text" placeholder="Prénom" class="w-full rounded border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gpj-600 mb-1">Grade</label>
                                <input v-model="newMilitaire.grade" type="text" placeholder="Grade" class="w-full rounded border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gpj-600 mb-1">Matricule</label>
                                <input v-model="newMilitaire.matricule" type="text" placeholder="Matricule" class="w-full rounded border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mt-6 pt-4 border-t border-gpj-100">
                    <button @click="ajouterMilitaire" :disabled="ajoutEnCours" class="px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="ajoutEnCours" class="pi pi-spin pi-spinner mr-1"></i>
                        Ajouter
                    </button>
                    <button @click="closeAddMilitaireModal" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale retour phase -->
        <div v-if="showRetourModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-5 sm:p-6 max-w-md w-full shadow-xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                        <i class="pi pi-undo text-amber-600"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800">Retour à la phase précédente</h3>
                </div>
                <p class="text-sm text-gpj-600 mb-2">Vous allez supprimer la phase <strong>{{ phaseToRetour?.libelle || phaseToRetour?.phase_type?.libelle }}</strong> et revenir à la phase précédente.</p>
                <p class="text-sm text-red-500 mb-6">⚠️ Toutes les données de cette phase seront supprimées.</p>
                <div class="flex gap-3 justify-end">
                    <button @click="showRetourModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">Annuler</button>
                    <button @click="retournerPhase" :disabled="retourProcessing" class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="retourProcessing" class="pi pi-spin pi-spinner mr-1"></i>Confirmer le retour
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Card, Badge } from '@/Components/GPJ';
import PhaseFormFields from '@/Components/Procedure/PhaseFormFields.vue';
import MilitaireDetail from '@/Components/Procedure/MilitaireDetail.vue';
import SearchSelect from '@/Components/GPJ/SearchSelect.vue';

const page = usePage();
const props = defineProps({ 
    procedure: Object, 
    phaseTypes: Array, 
    parquets: { type: Array, default: () => ['BAMAKO','MOPTI','GAO','KAYES'] }, 
    infractions: { type: Array, default: () => [] },
    grades: { type: Array, default: () => [] },
    armees: { type: Array, default: () => [] }
});

// ====== INFRACTIONS ======
// Utiliser les infractions passées en prop
const allInfractions = ref(props.infractions || []);

// Log pour déboguer
console.log('📦 Infractions reçues dans Show:', allInfractions.value);

// ====== GESTION DES ERREURS FLASH ======
const flashError = ref(null);
const flashSuccess = ref(null);
const flashMessage = ref(null);

const showFlash = (t, m) => { 
    flashMessage.value = { type: t, message: m }; 
    setTimeout(() => flashMessage.value = null, 4000); 
};

watch(() => page.props.flash?.error, (error) => {
    if (error) {
        flashError.value = error;
        console.error('❌ Erreur flash:', error);
        setTimeout(() => flashError.value = null, 8000);
        showFlash('error', error);
    }
}, { immediate: true });

watch(() => page.props.flash?.success, (success) => {
    if (success) {
        flashSuccess.value = success;
        console.log('✅ Succès:', success);
        setTimeout(() => flashSuccess.value = null, 5000);
        showFlash('success', success);
    }
}, { immediate: true });

onMounted(() => { 
    if (page.props.flash?.success) {
        flashSuccess.value = page.props.flash.success;
        showFlash('success', page.props.flash.success);
        setTimeout(() => flashSuccess.value = null, 5000);
    }
    if (page.props.flash?.error) {
        flashError.value = page.props.flash.error;
        showFlash('error', page.props.flash.error);
        setTimeout(() => flashError.value = null, 8000);
    }
});

// ====== PERMISSIONS ======
const peutValider = computed(() => ['CDS','CDD','SD'].includes(page.props.auth.user.role));

// ====== FORMATAGE ======
const formatDate = (d) => {
    if (!d) return 'Non défini';
    const date = new Date(d);
    if (isNaN(date.getTime())) return 'Non défini';
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const formatDateForInput = (d) => { 
    if (!d) return ''; 
    const dt = new Date(d); 
    if (isNaN(dt.getTime())) return ''; 
    return `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2,'0')}-${String(dt.getDate()).padStart(2,'0')}`; 
};

const formatLabel = (c) => (c || '').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

// ====== PHASES ======
const phasesEffectuees = computed(() => { 
    if (!props.procedure.procedure_phases) return []; 
    return [...new Set(props.procedure.procedure_phases.map(h => h.phaseType?.slug).filter(Boolean))]; 
});

const phasesDisponibles = computed(() => props.phaseTypes.filter(pt => !phasesEffectuees.value.includes(pt.slug)));

// ====== RECHARGEMENT ======
const rechargerProcedure = () => {
    router.reload({ preserveState: true });
};

// ====== AJOUTER UN MILITAIRE ======
const showAddMilitaireModal = ref(false);
const ajoutEnCours = ref(false);
const optionsMilitaires = ref([]);
const newMilitaire = ref({
    militaire_id: null,
    nom: '',
    prenom: '',
    grade: '',
    matricule: ''
});

const openAddMilitaireModal = () => {
    newMilitaire.value = { militaire_id: null, nom: '', prenom: '', grade: '', matricule: '' };
    showAddMilitaireModal.value = true;
};

const closeAddMilitaireModal = () => {
    showAddMilitaireModal.value = false;
    newMilitaire.value = { militaire_id: null, nom: '', prenom: '', grade: '', matricule: '' };
};

const rechercherMilitaires = async (query) => {
    if (!query || query.length < 2) {
        optionsMilitaires.value = [];
        return;
    }
    try {
        const response = await fetch(`/api/militaires/search?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        optionsMilitaires.value = data;
    } catch (e) {
        console.error('Erreur recherche:', e);
    }
};

const onMilitaireChange = () => {
    const mil = newMilitaire.value;
    if (mil.militaire_id) {
        const selected = optionsMilitaires.value.find(m => m.value === mil.militaire_id);
        if (selected) {
            mil.nom = selected.label.split(' ')[0] || '';
            mil.prenom = selected.label.split(' ').slice(1).join(' ') || '';
            mil.matricule = selected.sublabel || '';
        }
    }
};

const ajouterMilitaire = () => {
    ajoutEnCours.value = true;
    
    router.post(route('procedures.militaire.ajouter', props.procedure.id), {
        militaire_id: newMilitaire.value.militaire_id,
        nom: newMilitaire.value.nom,
        prenom: newMilitaire.value.prenom,
        grade: newMilitaire.value.grade,
        matricule: newMilitaire.value.matricule
    }, {
        onSuccess: () => {
            ajoutEnCours.value = false;
            closeAddMilitaireModal();
            rechargerProcedure();
            flashSuccess.value = 'Militaire ajouté avec succès';
            setTimeout(() => flashSuccess.value = null, 5000);
        },
        onError: (errors) => {
            ajoutEnCours.value = false;
            flashError.value = 'Erreur lors de l\'ajout: ' + JSON.stringify(errors);
            setTimeout(() => flashError.value = null, 8000);
        }
    });
};

// ====== FORMULAIRES D'ÉDITION ======
const editParquet = ref(false); 
const editForm = ref({ parquet_competent: '', date_ouverture: '' });

const saveParquet = () => {
    router.patch(route('procedures.update-parquet', props.procedure.id), { 
        parquet_competent: editForm.value.parquet_competent 
    }, { 
        onSuccess: () => editParquet.value = false, 
        preserveScroll: true 
    });
};

const editDateOuverture = ref(false);

const saveDateOuverture = () => {
    router.patch(route('procedures.update-date-ouverture', props.procedure.id), { 
        date_ouverture: editForm.value.date_ouverture 
    }, { 
        onSuccess: () => editDateOuverture.value = false, 
        preserveScroll: true 
    });
};

// ====== AJOUT DE PHASE ======
const formProcessing = ref(false);
const phaseForm = ref({ 
    phase_type_id: '', 
    phase_personnalisee: '', 
    date_phase: '', 
    description: '', 
    champs: [], 
    personnes: [], 
    evenements: [], 
    references: [], 
    options_cocher: [], 
    pieces_jointes: [{ nom: '', description: '', contexte: '' }] 
});

const onPhaseTypeChange = () => { 
    phaseForm.value.champs = []; 
    phaseForm.value.personnes = []; 
    phaseForm.value.evenements = []; 
    phaseForm.value.references = []; 
    phaseForm.value.options_cocher = []; 
    phaseForm.value.pieces_jointes = [{ nom: '', description: '', contexte: '' }]; 
    
    const tid = phaseForm.value.phase_type_id; 
    if (tid && tid !== 'autre') { 
        const pt = props.phaseTypes.find(p => p.id == tid); 
        if (pt) { 
            if (pt.slug === 'communique') { 
                phaseForm.value.personnes = [{ nom: '', prenom: '', profession: '', autre: '' }]; 
                phaseForm.value.evenements = [{ nom: '', date_evenement: '', description: '' }]; 
                phaseForm.value.champs = [
                    { cle: 'origine', valeur: '', type: 'text' },
                    { cle: 'numero', valeur: '', type: 'text' },
                    { cle: 'date_communique', valeur: phaseForm.value.date_phase, type: 'date' }
                ]; 
            } else if (pt.slug === 'mise_a_disposition') { 
                phaseForm.value.references = [{ libelle: '', description: '' }]; 
                phaseForm.value.champs = [{ cle: 'date_mad', valeur: phaseForm.value.date_phase, type: 'date' }]; 
            } else if (pt.slug === 'ordre_de_poursuite') { 
                phaseForm.value.options_cocher = [
                    { libelle: 'Détenu', est_coche: false },
                    { libelle: 'Non détenu', est_coche: false },
                    { libelle: 'Citation directe', est_coche: false },
                    { libelle: 'Information', est_coche: false },
                    { libelle: 'Autre', est_coche: false }
                ]; 
                phaseForm.value.champs = [
                    { cle: 'reglement', valeur: '', type: 'text' },
                    { cle: 'ordonnance_juge_instruction', valeur: '', type: 'text' },
                    { cle: 'jugement', valeur: '', type: 'text' },
                    { cle: 'peine', valeur: '', type: 'text' },
                    { cle: 'voix_de_recours', valeur: '', type: 'text' },
                    { cle: 'arret_rendu', valeur: '', type: 'text' },
                    { cle: 'voix_recours_arret', valeur: '', type: 'text' }
                ]; 
            } 
        } 
    } 
};

const ajouterPhase = () => { 
    if (!phaseForm.value.phase_type_id || !phaseForm.value.date_phase) {
        flashError.value = 'Champs obligatoires: Type de phase et Date';
        setTimeout(() => flashError.value = null, 5000);
        return;
    }
    formProcessing.value = true; 
    router.post(route('procedures.ajouter-phase', props.procedure.id), { ...phaseForm.value }, { 
        onSuccess: () => { 
            formProcessing.value = false; 
            flashSuccess.value = 'Phase ajoutée avec succès';
            setTimeout(() => flashSuccess.value = null, 5000);
            phaseForm.value = { 
                phase_type_id: '', 
                phase_personnalisee: '', 
                date_phase: '', 
                description: '', 
                champs: [], 
                personnes: [], 
                evenements: [], 
                references: [], 
                options_cocher: [], 
                pieces_jointes: [{ nom: '', description: '', contexte: '' }] 
            }; 
        }, 
        onError: (errors) => {
            formProcessing.value = false;
            flashError.value = 'Erreur lors de l\'ajout de la phase: ' + JSON.stringify(errors);
            setTimeout(() => flashError.value = null, 8000);
        }, 
        preserveScroll: true 
    }); 
};

// ====== ÉDITION DE PHASE ======
const editingPhaseId = ref(null); 
const editPhaseProcessing = ref(false);
const editPhaseForm = ref({ 
    description: '', 
    date_phase: '', 
    champs: [], 
    personnes: [], 
    evenements: [], 
    references: [], 
    options_cocher: [], 
    pieces_jointes: [] 
});
const showEditCustomField = ref(false); 
const editCustomField = ref({ cle: '', type: 'text', valeur: '' });

const startEditPhase = (phase) => { 
    editingPhaseId.value = phase.id; 
    let pj = (phase.pieces_jointes || []).map(p => ({ 
        id: p.id, 
        nom: p.nom, 
        description: p.description || '', 
        chemin_fichier: p.chemin_fichier || null 
    })); 
    if (!pj.length) pj = [{ nom: '', description: '', chemin_fichier: null }]; 
    editPhaseForm.value = { 
        description: phase.description || '', 
        date_phase: formatDateForInput(phase.date_phase), 
        champs: (phase.champs || []).map(c => ({ 
            id: c.id, 
            cle: c.cle, 
            valeur: c.valeur || '', 
            type: c.type || 'text' 
        })), 
        personnes: (phase.personnes || []).map(p => ({ 
            id: p.id, 
            nom: p.nom, 
            prenom: p.prenom, 
            profession: p.profession || '', 
            autre: p.autre || '' 
        })), 
        evenements: (phase.evenements || []).map(e => ({ 
            id: e.id, 
            nom: e.nom, 
            date_evenement: formatDateForInput(e.date_evenement), 
            description: e.description || '' 
        })), 
        references: (phase.references || []).map(r => ({ 
            id: r.id, 
            libelle: r.libelle, 
            description: r.description || '' 
        })), 
        options_cocher: (phase.options_cocher || []).map(o => ({ 
            id: o.id, 
            libelle: o.libelle, 
            est_coche: o.est_coche, 
            _custom: false 
        })), 
        pieces_jointes: pj, 
    }; 
    showEditCustomField.value = false; 
    editCustomField.value = { cle: '', type: 'text', valeur: '' }; 
};

const cancelEditPhase = () => { 
    editingPhaseId.value = null; 
    showEditCustomField.value = false; 
};

const addCustomFieldToEdit = () => { 
    if (!editCustomField.value.cle.trim()) {
        flashError.value = 'Nom du champ requis';
        setTimeout(() => flashError.value = null, 5000);
        return;
    }
    editPhaseForm.value.champs.push({ 
        cle: editCustomField.value.cle.trim().toLowerCase().replace(/\s+/g, '_'), 
        valeur: '', 
        type: editCustomField.value.type, 
        _custom: true 
    }); 
    showEditCustomField.value = false; 
    editCustomField.value = { cle: '', type: 'text', valeur: '' }; 
};

const onEditFileChange = (e, i) => { 
    const f = e.target.files[0]; 
    if (f && editPhaseForm.value.pieces_jointes?.[i]) {
        editPhaseForm.value.pieces_jointes[i].fichier = f;
    }
};

const savePhaseEdit = (phaseId) => { 
    editPhaseProcessing.value = true; 
    router.put(route('procedures.update-phase', { procedure: props.procedure.id, phase: phaseId }), { 
        ...editPhaseForm.value 
    }, { 
        onSuccess: () => { 
            editPhaseProcessing.value = false; 
            editingPhaseId.value = null; 
            showEditCustomField.value = false;
            flashSuccess.value = 'Phase mise à jour avec succès';
            setTimeout(() => flashSuccess.value = null, 5000);
        }, 
        onError: (errors) => {
            editPhaseProcessing.value = false;
            flashError.value = 'Erreur lors de la mise à jour de la phase: ' + JSON.stringify(errors);
            setTimeout(() => flashError.value = null, 8000);
        }, 
        preserveScroll: true 
    }); 
};

// ====== RETOUR PHASE ======
const showRetourModal = ref(false); 
const retourProcessing = ref(false); 
const phaseToRetour = ref(null);

const confirmRetourPhase = (phase) => { 
    phaseToRetour.value = phase; 
    showRetourModal.value = true; 
};

const retournerPhase = () => { 
    if (!phaseToRetour.value) return; 
    retourProcessing.value = true; 
    router.delete(route('procedures.retourner-phase', { procedure: props.procedure.id, phase: phaseToRetour.value.id }), { 
        onSuccess: () => { 
            retourProcessing.value = false; 
            showRetourModal.value = false; 
            phaseToRetour.value = null;
            flashSuccess.value = 'Retour à la phase précédente effectué';
            setTimeout(() => flashSuccess.value = null, 5000);
        }, 
        onError: (errors) => {
            retourProcessing.value = false;
            flashError.value = 'Erreur lors du retour de phase: ' + JSON.stringify(errors);
            setTimeout(() => flashError.value = null, 8000);
        }, 
        preserveScroll: true 
    }); 
};
</script>
<script>export default { layout: null };</script>