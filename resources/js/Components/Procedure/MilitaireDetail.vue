<template>
    <div class="p-4 bg-gpj-50 dark:bg-gpj-800 rounded-lg border border-gpj-200 dark:border-gpj-700">
        <!-- En-tête du militaire -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gpj-100 dark:bg-gpj-700 flex items-center justify-center text-gpj-600 dark:text-gpj-300 font-bold text-sm">
                    {{ getInitiales() }}
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-gpj-800 dark:text-white">{{ getNomComplet() }}</span>
                        <span v-if="estPrincipal" class="text-[10px] bg-emerald-100 text-emerald-600 px-2 py-0.5 rounded-full">Principal</span>
                        <span v-if="procedureMilitaire?.est_nouveau" class="text-[10px] bg-amber-100 text-amber-600 px-2 py-0.5 rounded-full">Nouveau</span>
                        <Badge v-if="typePersonnel === 'civil'" variant="primary" size="sm">Civil</Badge>
                    </div>
                    <p class="text-xs text-gpj-400">{{ getMatricule() }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link 
                    v-if="peutModifier && procedureMilitaire?.militaire_id"
                    :href="getEditUrl()"
                    class="text-gpj-400 hover:text-gpj-600 text-xs transition-colors"
                    title="Modifier les informations du militaire"
                >
                    <i class="pi pi-pencil"></i>
                </Link>
                <button 
                    v-if="peutSupprimer"
                    @click="confirmDeleteMilitaire"
                    class="text-red-400 hover:text-red-600 text-xs transition-colors"
                    title="Supprimer ce militaire de la procédure"
                >
                    <i class="pi pi-trash"></i>
                </button>
                <a 
                    v-if="procedureMilitaire?.militaire_id"
                    :href="route('militaires.casier', procedureMilitaire.militaire_id)" 
                    target="_blank" 
                    class="text-gpj-400 hover:text-gpj-600 text-xs"
                    title="Imprimer le casier"
                >
                    <i class="pi pi-print"></i>
                </a>
            </div>
        </div>

        <!-- Mode affichage -->
        <div class="space-y-3">
            <!-- Informations du militaire -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm bg-white dark:bg-gpj-900 p-3 rounded-lg">
                <div><span class="text-gpj-400">Grade / Profession:</span> <span class="font-medium">{{ getGradeOuProfession() }}</span></div>
                <div><span class="text-gpj-400">Unité:</span> <span class="font-medium">{{ getUnite() }}</span></div>
                <div><span class="text-gpj-400">Genre:</span> <span class="font-medium">{{ getGenre() }}</span></div>
                <div><span class="text-gpj-400">Armée:</span> <span class="font-medium">{{ getArmee() }}</span></div>
                <div><span class="text-gpj-400">Statut:</span> <Badge :variant="statutVariant(getStatut())" size="sm">{{ getStatut() }}</Badge></div>
                <div><span class="text-gpj-400">Date naissance:</span> <span class="font-medium">{{ formatDate(getDateNaissance()) }}</span></div>
            </div>

            <!-- Infractions -->
            <div class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-list mr-1"></i> Infractions
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirEditionInfractions"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-pencil text-xs mr-1"></i> Modifier
                    </button>
                </div>
                <div v-if="getInfractionsDisplay().length === 0" class="text-xs text-gpj-400 py-1">Aucune infraction</div>
                <div class="flex flex-wrap gap-1">
                    <Badge v-for="inf in getInfractionsDisplay()" :key="inf.id" variant="neutral" size="sm" class="text-[10px]">
                        {{ inf.libelle }}
                    </Badge>
                </div>
            </div>

            <!-- Fautes militaires par catégorie -->
            <div v-if="typePersonnel === 'militaire'" class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-exclamation-triangle mr-1"></i> Fautes militaires
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirGestionFautes"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-cog text-xs mr-1"></i> Gérer
                    </button>
                </div>
                
                <div v-if="getFautesAffichees().length === 0" class="text-xs text-gpj-400 py-1">
                    Aucune faute
                </div>
                
                <!-- Affichage des fautes par catégorie -->
                <div v-for="(groupe, categorie) in fautesParCategorie" :key="categorie" class="mb-2">
                    <span class="text-xs font-semibold text-gpj-500 block mb-1">{{ categorie }}</span>
                    <div class="flex flex-wrap gap-1">
                        <span v-for="faute in groupe" :key="faute.id" class="text-xs bg-gpj-100 px-2 py-0.5 rounded">
                            {{ faute.libelle }}
                            <span v-if="faute.code" class="text-[10px] text-gpj-400 ml-1">({{ faute.code }})</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Parties civiles -->
            <div class="border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-users mr-1"></i> Parties civiles
                    </label>
                    <button 
                        v-if="peutModifier"
                        @click="ouvrirEditionPartiesCiviles"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-pencil text-xs mr-1"></i> Modifier acteurs
                    </button>
                </div>
                <div v-if="getPartiesCiviles().length === 0" class="text-xs text-gpj-400 py-1">Aucune partie civile</div>
                <div v-for="(pc, pi) in getPartiesCiviles()" :key="'pc-'+pi" class="text-xs text-gpj-600">
                    - {{ pc.type === 'Structure' ? 'Structure: ' + pc.nom : pc.nom + ' ' + (pc.prenom || '') }}
                </div>
            </div>

            <!-- Témoins -->
            <div class="border-t border-gpj-100 pt-3">
                <label class="text-xs font-medium text-gpj-600 mb-2 block">
                    <i class="pi pi-users mr-1"></i> Témoins
                </label>
                <div v-if="getPersonnes('temoins').length === 0" class="text-xs text-gpj-400 py-1">Aucun témoin</div>
                <div v-for="(pc, pi) in getPersonnes('temoins')" :key="'t-'+pi" class="text-xs text-gpj-600">
                    - {{ pc.nom }} {{ pc.prenom || '' }}
                </div>
            </div>

            <!-- Civile Responsable -->
            <div class="border-t border-gpj-100 pt-3">
                <label class="text-xs font-medium text-gpj-600 mb-2 block">
                    <i class="pi pi-user mr-1"></i> Civile responsable
                </label>
                <div v-if="getPersonnes('civile_responsables').length === 0" class="text-xs text-gpj-400 py-1">Aucune civile responsable</div>
                <div v-for="(pc, pi) in getPersonnes('civile_responsables')" :key="'cr-'+pi" class="text-xs text-gpj-600">
                    - {{ pc.nom }} {{ pc.prenom || '' }}
                </div>
            </div>

            <!-- Garants -->
            <div class="border-t border-gpj-100 pt-3">
                <label class="text-xs font-medium text-gpj-600 mb-2 block">
                    <i class="pi pi-shield mr-1"></i> Garants
                </label>
                <div v-if="getPersonnes('garants').length === 0" class="text-xs text-gpj-400 py-1">Aucun garant</div>
                <div v-for="(pc, pi) in getPersonnes('garants')" :key="'g-'+pi" class="text-xs text-gpj-600">
                    - {{ pc.nom }} {{ pc.prenom || '' }}
                </div>
            </div>

            <!-- Avocat -->
            <div class="border-t border-gpj-100 pt-3">
                <label class="text-xs font-medium text-gpj-600 mb-2 block">
                    <i class="pi pi-briefcase mr-1"></i> Avocat
                </label>
                <div v-if="getPersonnes('avocats').length === 0" class="text-xs text-gpj-400 py-1">Aucun avocat</div>
                <div v-for="(pc, pi) in getPersonnes('avocats')" :key="'av-'+pi" class="text-xs text-gpj-600">
                    - {{ pc.nom }} {{ pc.prenom || '' }}
                </div>
            </div>
        </div>

        <!-- Modale édition infractions -->
        <div v-if="showEditInfractions" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Modifier les infractions</h3>
                <div class="space-y-2">
                    <div v-for="inf in allInfractions" :key="inf.id" class="flex items-center gap-2 p-2 hover:bg-gpj-50 rounded">
                        <input 
                            type="checkbox" 
                            :value="inf.id" 
                            v-model="editInfractionsForm" 
                            class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500"
                        />
                        <span class="text-sm">{{ inf.libelle }}</span>
                        <span class="text-xs text-gpj-400">{{ inf.code_infraction }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gpj-100">
                    <button @click="sauvegarderInfractions" :disabled="savingInfractions" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="savingInfractions" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer
                    </button>
                    <button @click="showEditInfractions = false" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- MODALE GESTION DES FAUTES -->
        <div v-if="showGestionFautes" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gpj-800 dark:text-white">Gestion des fautes militaires</h3>
                        <p class="text-sm text-gpj-500">Sélectionnez les fautes pour {{ getNomComplet() }}</p>
                    </div>
                    <button @click="fermerGestionFautes" class="text-gpj-400 hover:text-gpj-600 transition-colors">
                        <i class="pi pi-times text-lg"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Colonne gauche : Catégories -->
                    <div class="border-r border-gpj-200 pr-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gpj-700">Catégories</h4>
                            <div class="flex items-center gap-2">
                                <button
                                    @click="loadCategoriesFautes"
                                    class="px-3 py-1.5 bg-gpj-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-1"
                                    title="Rafraîchir"
                                >
                                    <i class="pi pi-refresh text-xs"></i>
                                </button>
                                <button
                                    @click="ouvrirModalCreationCategorie"
                                    class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors flex items-center gap-1"
                                >
                                    <i class="pi pi-plus text-xs"></i> Nouvelle
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-2 max-h-100 overflow-y-auto">
                            <div
                                v-for="cat in categoriesFautes"
                                :key="cat.id"
                                class="flex items-center justify-between p-2 rounded-lg cursor-pointer transition-colors"
                                :class="{
                                    'bg-gpj-100 border-gpj-400': categorieSelectionnee === cat.id,
                                    'hover:bg-gpj-50 border-transparent': categorieSelectionnee !== cat.id
                                }"
                                @click="categorieSelectionnee = cat.id"
                            >
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gpj-700 truncate">{{ cat.libelle }}</p>
                                    <p class="text-xs text-gpj-400">{{ cat.fautes?.length || 0 }} faute(s)</p>
                                </div>
                                <div class="flex items-center gap-1 ml-2" @click.stop>
                                    <button
                                        @click="ouvrirModalEditionCategorie(cat)"
                                        class="text-gpj-400 hover:text-gpj-600 text-xs p-1"
                                        title="Modifier"
                                    >
                                        <i class="pi pi-pencil"></i>
                                    </button>
                                    <button
                                        @click="supprimerCategorie(cat.id)"
                                        class="text-red-400 hover:text-red-600 text-xs p-1"
                                        title="Supprimer"
                                    >
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="categoriesFautes.length === 0" class="text-center py-4 text-gpj-400 text-sm">
                                <i class="pi pi-inbox text-2xl block mb-1"></i>
                                Aucune catégorie
                            </div>
                        </div>
                    </div>

                    <!-- Colonne droite : Fautes de la catégorie sélectionnée -->
                    <div class="pl-4">
                        <div v-if="categorieSelectionnee" class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gpj-700">
                                Fautes : {{ getCategorieLibelle(categorieSelectionnee) }}
                            </h4>
                            <button
                                @click="ouvrirModalCreationFaute"
                                class="px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors flex items-center gap-1"
                            >
                                <i class="pi pi-plus text-xs"></i> Ajouter
                            </button>
                        </div>
                        
                        <div v-if="!categorieSelectionnee" class="text-center py-8 text-gpj-400 text-sm">
                            <i class="pi pi-arrow-left text-2xl block mb-1"></i>
                            Sélectionnez une catégorie
                        </div>
                        
                        <div v-else class="space-y-2 max-h-100 overflow-y-auto">
                            <div
                                v-for="faute in getFautesByCategorie(categorieSelectionnee)"
                                :key="faute.id"
                                class="flex items-center gap-2 p-2 rounded-lg border"
                                :class="{
                                    'bg-gpj-50 border-gpj-400': (fautesSelectionnees || []).includes(faute.id),
                                    'border-gpj-200': !(fautesSelectionnees || []).includes(faute.id)
                                }"
                            >
                                <input
                                    type="checkbox"
                                    :value="faute.id"
                                    v-model="fautesSelectionnees"
                                    class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500"
                                />
                                <span class="text-sm flex-1">{{ faute.libelle }}</span>
                                <span v-if="faute.code" class="text-xs text-gpj-400">{{ faute.code }}</span>
                                <div class="flex items-center gap-1">
                                    <button
                                        @click="ouvrirModalEditionFaute(faute)"
                                        class="text-gpj-400 hover:text-gpj-600 text-xs p-1"
                                        title="Modifier"
                                    >
                                        <i class="pi pi-pencil"></i>
                                    </button>
                                    <button
                                        @click="supprimerFauteDb(faute.id)"
                                        class="text-red-400 hover:text-red-600 text-xs p-1"
                                        title="Supprimer"
                                    >
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div v-if="getFautesByCategorie(categorieSelectionnee).length === 0" class="text-center py-4 text-gpj-400 text-sm">
                                <i class="pi pi-inbox text-2xl block mb-1"></i>
                                Aucune faute dans cette catégorie
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center gap-3 pt-4 mt-4 border-t border-gpj-100">
                    <button @click="sauvegarderFautes" :disabled="sauvegardeFautesEnCours"
                        class="px-4 py-2 bg-emerald-500 text-white text-sm font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="sauvegardeFautesEnCours" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer les sélections
                    </button>
                    <button @click="fermerGestionFautes"
                        class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale création/modification catégorie -->
        <div v-if="showCategorieModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">
                    {{ categorieEditId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                </h3>
                <form @submit.prevent="sauvegarderCategorie" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                        <input
                            v-model="categorieForm.libelle"
                            type="text"
                            required
                            placeholder="Ex: Manquement à la discipline"
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Description</label>
                        <textarea
                            v-model="categorieForm.description"
                            rows="2"
                            placeholder="Description de la catégorie..."
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-3 border-t border-gpj-100">
                        <button type="button" @click="fermerModalCategorie"
                            class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                            Annuler
                        </button>
                        <button type="submit" :disabled="categorieEnCours"
                            class="flex-1 px-4 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 disabled:opacity-50 cursor-pointer">
                            <i v-if="categorieEnCours" class="pi pi-spin pi-spinner mr-1"></i>
                            {{ categorieEditId ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modale création/modification faute -->
        <div v-if="showFauteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full shadow-xl">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">
                    {{ fauteEditId ? 'Modifier la faute' : 'Nouvelle faute' }}
                </h3>
                <form @submit.prevent="sauvegarderFaute" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Catégorie <span class="text-red-500">*</span></label>
                        <select
                            v-model="fauteForm.categorie_faute_id"
                            required
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        >
                            <option value="">Sélectionner une catégorie</option>
                            <option v-for="cat in categoriesFautes" :key="cat.id" :value="cat.id">
                                {{ cat.libelle }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                        <input
                            v-model="fauteForm.libelle"
                            type="text"
                            required
                            placeholder="Ex: Insoumission"
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Code</label>
                        <input
                            v-model="fauteForm.code"
                            type="text"
                            placeholder="Ex: F-001"
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gpj-700 mb-1">Description</label>
                        <textarea
                            v-model="fauteForm.description"
                            rows="2"
                            placeholder="Description de la faute..."
                            class="w-full rounded-lg border border-gpj-200 text-sm py-2 px-3 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                        ></textarea>
                    </div>
                    <div class="flex items-center gap-3 pt-3 border-t border-gpj-100">
                        <button type="button" @click="fermerModalFaute"
                            class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                            Annuler
                        </button>
                        <button type="submit" :disabled="fauteEnCours"
                            class="flex-1 px-4 py-2 bg-gpj-500 text-white text-sm font-medium rounded-lg hover:bg-gpj-600 disabled:opacity-50 cursor-pointer">
                            <i v-if="fauteEnCours" class="pi pi-spin pi-spinner mr-1"></i>
                            {{ fauteEditId ? 'Modifier' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modale édition acteurs annexes -->
        <div v-if="showEditPartiesCiviles" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gpj-800 dark:text-white mb-4">Modifier les acteurs annexes</h3>
                
                <!-- Parties civiles -->
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gpj-700 mb-2">Parties civiles</h4>
                    <div class="space-y-2">
                        <div v-for="(pc, pi) in editPartiesCivilesForm" :key="'epc-'+pi" class="grid grid-cols-1 sm:grid-cols-3 gap-2 p-2 bg-gpj-50 rounded">
                            <select v-model="pc.type" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                                <option value="Personne">Personne</option>
                                <option value="Structure">Structure</option>
                            </select>
                            <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <div class="flex items-center gap-2">
                                <input v-if="pc.type === 'Personne'" v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                                <button @click="editPartiesCivilesForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs shrink-0"><i class="pi pi-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="editPartiesCivilesForm.push({ type: 'Personne', nom: '', prenom: '' })" class="text-xs text-gpj-500 hover:text-gpj-700 mt-2">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter une partie civile
                    </button>
                </div>

                <!-- Témoins -->
                <div class="mb-4 border-t border-gpj-100 pt-4">
                    <h4 class="text-sm font-semibold text-gpj-700 mb-2">Témoins</h4>
                    <div class="space-y-2">
                        <div v-for="(pc, pi) in editTemoinsForm" :key="'etm-'+pi" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 bg-gpj-50 rounded">
                            <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <div class="flex items-center gap-2">
                                <input v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                                <button @click="editTemoinsForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs shrink-0"><i class="pi pi-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="editTemoinsForm.push({ nom: '', prenom: '' })" class="text-xs text-gpj-500 hover:text-gpj-700 mt-2">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter un témoin
                    </button>
                </div>

                <!-- Civile responsable -->
                <div class="mb-4 border-t border-gpj-100 pt-4">
                    <h4 class="text-sm font-semibold text-gpj-700 mb-2">Civile responsable</h4>
                    <div class="space-y-2">
                        <div v-for="(pc, pi) in editCivileResponsablesForm" :key="'ecr-'+pi" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 bg-gpj-50 rounded">
                            <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <div class="flex items-center gap-2">
                                <input v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                                <button @click="editCivileResponsablesForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs shrink-0"><i class="pi pi-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="editCivileResponsablesForm.push({ nom: '', prenom: '' })" class="text-xs text-gpj-500 hover:text-gpj-700 mt-2">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter civile responsable
                    </button>
                </div>

                <!-- Garants -->
                <div class="mb-4 border-t border-gpj-100 pt-4">
                    <h4 class="text-sm font-semibold text-gpj-700 mb-2">Garants</h4>
                    <div class="space-y-2">
                        <div v-for="(pc, pi) in editGarantsForm" :key="'eg-'+pi" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 bg-gpj-50 rounded">
                            <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <div class="flex items-center gap-2">
                                <input v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                                <button @click="editGarantsForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs shrink-0"><i class="pi pi-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="editGarantsForm.push({ nom: '', prenom: '' })" class="text-xs text-gpj-500 hover:text-gpj-700 mt-2">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter un garant
                    </button>
                </div>

                <!-- Avocats -->
                <div class="mb-4 border-t border-gpj-100 pt-4">
                    <h4 class="text-sm font-semibold text-gpj-700 mb-2">Avocats</h4>
                    <div class="space-y-2">
                        <div v-for="(pc, pi) in editAvocatsForm" :key="'ea-'+pi" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-2 bg-gpj-50 rounded">
                            <input v-model="pc.nom" placeholder="Nom / Cabinet *" class="rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                            <div class="flex items-center gap-2">
                                <input v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-sm py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                                <button @click="editAvocatsForm.splice(pi, 1)" class="text-red-400 hover:text-red-600 text-xs shrink-0"><i class="pi pi-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="editAvocatsForm.push({ nom: '', prenom: '' })" class="text-xs text-gpj-500 hover:text-gpj-700 mt-2">
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter un avocat
                    </button>
                </div>

                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gpj-100">
                    <button @click="sauvegarderPartiesCiviles" :disabled="savingPartiesCiviles" class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="savingPartiesCiviles" class="pi pi-spin pi-spinner mr-1"></i>
                        Enregistrer
                    </button>
                    <button @click="showEditPartiesCiviles = false" class="px-3 py-1.5 border border-gpj-200 text-gpj-600 text-xs rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale confirmation suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-md w-full shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 flex items-center justify-center shrink-0">
                        <i class="pi pi-exclamation-triangle text-red-600 dark:text-red-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gpj-800 dark:text-white">Confirmer la suppression</h3>
                </div>
                <p class="text-sm text-gpj-600 dark:text-gpj-300 mb-2">
                    Êtes-vous sûr de vouloir retirer le militaire suivant de la procédure ?
                </p>
                <p class="text-sm font-bold text-gpj-800 dark:text-white mb-4">
                    {{ getNomComplet() }} ({{ getMatricule() }})
                </p>
                <p class="text-sm text-red-500 mb-6">
                    ⚠️ Cette action supprimera toutes les données associées à ce militaire (infractions, fautes, parties civiles).
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gpj-200 text-gpj-600 text-sm rounded-lg hover:bg-gpj-50 cursor-pointer">
                        Annuler
                    </button>
                    <button @click="supprimerMilitaire" :disabled="deleting" class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 disabled:opacity-50 cursor-pointer">
                        <i v-if="deleting" class="pi pi-spin pi-spinner mr-1"></i>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Badge } from '@/Components/GPJ';

const props = defineProps({
    procedureMilitaire: {
        type: Object,
        required: true
    },
    procedureId: {
        type: Number,
        required: true
    },
    estPrincipal: {
        type: Boolean,
        default: false
    },
    allInfractions: {
        type: Array,
        default: () => []
    },
    allFautes: {
        type: Array,
        default: () => []
    },
    peutModifier: {
        type: Boolean,
        default: false
    },
    grades: {
        type: Array,
        default: () => []
    },
    armees: {
        type: Array,
        default: () => []
    },
    totalMilitaires: {
        type: Number,
        default: 1
    },
    estPlurielle: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['updated', 'deleted', 'infraction-created']);

// ====== TYPE PERSONNEL ======
const typePersonnel = computed(() => props.procedureMilitaire?.type_personnel || 'militaire');

// ====== ÉTATS ======
const showEditInfractions = ref(false);
const showEditPartiesCiviles = ref(false);
const savingInfractions = ref(false);
const savingPartiesCiviles = ref(false);
const showDeleteModal = ref(false);
const deleting = ref(false);

// ====== ÉTATS POUR LA GESTION DES FAUTES ======
const showGestionFautes = ref(false);
const sauvegardeFautesEnCours = ref(false);
const currentProcedureMilitaireId = ref(null);
const fautesSelectionnees = ref([]);
const categoriesFautes = ref([]);
const fautesMap = ref({});
const categorieSelectionnee = ref('');

// ====== ÉTATS POUR LES MODALES CRUD ======
const showCategorieModal = ref(false);
const categorieEditId = ref(null);
const categorieEnCours = ref(false);
const categorieForm = ref({ libelle: '', description: '' });

const showFauteModal = ref(false);
const fauteEditId = ref(null);
const fauteEnCours = ref(false);
const fauteForm = ref({ categorie_faute_id: '', libelle: '', code: '', description: '' });

// ====== MAP DES INFRACTIONS ======
const infractionsMap = computed(() => {
    const map = {};
    props.allInfractions.forEach(inf => {
        map[inf.id] = inf;
    });
    return map;
});

// ====== MAP DES FAUTES ======
const fautesMapLocal = computed(() => {
    const map = {};
    props.allFautes.forEach(faute => {
        map[faute.id] = faute;
    });
    return map;
});

// ====== COMPUTED ======
const peutSupprimer = computed(() => {
    return props.peutModifier && 
           props.estPlurielle && 
           props.totalMilitaires > 1;
});

// ====== RÉCUPÉRATION DES FAUTES AVEC LEURS NOMS ======
const getFautesAffichees = () => {
    const pm = props.procedureMilitaire;
    if (!pm.fautes_militaires || pm.fautes_militaires.length === 0) {
        return [];
    }
    
    const fauteIds = pm.fautes_militaires || [];
    const result = [];
    
    fauteIds.forEach(id => {
        const faute = props.allFautes.find(f => f.id === id);
        if (faute) {
            result.push(faute);
        } else {
            // Fallback: essayer de trouver dans la map
            const found = fautesMapLocal.value[id];
            if (found) {
                result.push(found);
            } else {
                // Si toujours pas trouvé, afficher un placeholder
                result.push({ 
                    id: id, 
                    libelle: 'Faute #' + id, 
                    code: '',
                    categorie: { libelle: 'Inconnue' }
                });
            }
        }
    });
    
    return result;
};

// ====== FAUTES PAR CATÉGORIE ======
const fautesParCategorie = computed(() => {
    const fautes = getFautesAffichees();
    if (fautes.length === 0) {
        return {};
    }
    
    const result = {};
    
    fautes.forEach(faute => {
        const categorie = faute.categorie?.libelle || 'Non catégorisé';
        if (!result[categorie]) {
            result[categorie] = [];
        }
        result[categorie].push(faute);
    });
    
    return result;
});

// ====== DONNÉES ======
const editInfractionsForm = ref([...(props.procedureMilitaire?.infractions || [])]);
const editPartiesCivilesForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.parties_civiles || [])));
const editTemoinsForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.temoins || [])));
const editCivileResponsablesForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.civile_responsables || [])));
const editGarantsForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.garants || [])));
const editAvocatsForm = ref(JSON.parse(JSON.stringify(props.procedureMilitaire?.avocats || [])));

// ====== CHARGEMENT DES CATÉGORIES DE FAUTES ======
const loadCategoriesFautes = async () => {
    try {
        console.log('📥 Chargement des catégories de fautes...');
        const response = await fetch('/api/categories-fautes');
        console.log('📥 Réponse status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('📥 Données reçues:', data);
        console.log('📥 Nombre de catégories:', data.length);
        
        categoriesFautes.value = data;
        
        // Créer une map pour les libellés
        data.forEach(cat => {
            cat.fautes?.forEach(faute => {
                fautesMap.value[faute.id] = faute.libelle;
            });
        });
        
        // Réinitialiser la sélection
        categorieSelectionnee.value = '';
        console.log('✅ Catégories chargées avec succès');
    } catch (e) {
        console.error('❌ Erreur chargement catégories fautes:', e);
    }
};

// ====== MÉTHODES ======
const getInitiales = () => {
    const pm = props.procedureMilitaire;
    if (pm.militaire) {
        return (pm.militaire.nom?.charAt(0) || '') + (pm.militaire.prenoms?.charAt(0) || '');
    }
    return (pm.nom_temp?.charAt(0) || '') + (pm.prenom_temp?.charAt(0) || '');
};

const getNomComplet = () => {
    const pm = props.procedureMilitaire;
    if (pm.militaire) {
        return pm.militaire.nom + ' ' + pm.militaire.prenoms;
    }
    return (pm.nom_temp || 'Nom inconnu') + ' ' + (pm.prenom_temp || '');
};

const getMatricule = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.matricule || pm.matricule_temp || 'Sans matricule';
};

const getGradeOuProfession = () => {
    const pm = props.procedureMilitaire;
    if (pm.type_personnel === 'civil') {
        return pm.militaire?.profession || pm.profession_temp || '-';
    }
    if (pm.militaire?.grade?.libelle) {
        return pm.militaire.grade.libelle;
    }
    if (pm.militaire?.grade) {
        return pm.militaire.grade;
    }
    return pm.grade_temp || '-';
};

const getUnite = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.unite || '-';
};

const getGenre = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.genre || '-';
};

const getArmee = () => {
    const pm = props.procedureMilitaire;
    if (pm.militaire?.armee) {
        return pm.militaire.armee;
    }
    if (pm.militaire?.armee_relation?.nom) {
        return pm.militaire.armee_relation.nom;
    }
    return '-';
};

const getStatut = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.statut || 'Actif';
};

const getDateNaissance = () => {
    const pm = props.procedureMilitaire;
    return pm.militaire?.date_naissance || null;
};

const getInfractionsDisplay = () => {
    const ids = props.procedureMilitaire?.infractions || [];
    if (ids.length === 0) return [];
    
    const found = ids
        .map(id => infractionsMap.value[id])
        .filter(inf => inf !== undefined);
    
    if (found.length > 0) {
        return found;
    }
    
    return ids.map(id => ({
        id: id,
        libelle: 'Infraction #' + id
    }));
};

const getPartiesCiviles = () => {
    return props.procedureMilitaire?.parties_civiles || [];
};

const getPersonnes = (field) => {
    return props.procedureMilitaire?.[field] || [];
};

const formatDate = (d) => {
    if (!d) return '-';
    const date = new Date(d);
    if (isNaN(date.getTime())) return '-';
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
};

const statutVariant = (s) => {
    const m = { 
        'En activité': 'success', 
        'Non activite': 'warning', 
        'En retraite': 'info', 
        'Radié': 'neutral' 
    };
    return m[s] || 'default';
};

// ====== URL DE RETOUR ======
const getEditUrl = () => {
    const currentPath = window.location.pathname || '/';
    return route('militaires.edit', { 
        militaire: props.procedureMilitaire.militaire_id, 
        from: currentPath 
    });
};

// ====== INFRACTIONS ======
const ouvrirEditionInfractions = () => {
    editInfractionsForm.value = [...(props.procedureMilitaire?.infractions || [])];
    showEditInfractions.value = true;
};

const sauvegarderInfractions = () => {
    savingInfractions.value = true;
    router.patch(route('procedure.militaire.infractions.update', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), { infractions: editInfractionsForm.value }, {
        onSuccess: () => {
            savingInfractions.value = false;
            showEditInfractions.value = false;
            emit('updated');
        },
        onError: () => {
            savingInfractions.value = false;
        }
    });
};

// ====== FAUTES - GESTION COMPLÈTE ======
const ouvrirGestionFautes = () => {
    currentProcedureMilitaireId.value = props.procedureMilitaire.id;
    fautesSelectionnees.value = [...(props.procedureMilitaire?.fautes_militaires || [])];
    showGestionFautes.value = true;
    loadCategoriesFautes();
};

const fermerGestionFautes = () => {
    showGestionFautes.value = false;
    currentProcedureMilitaireId.value = null;
    fautesSelectionnees.value = [];
    categorieSelectionnee.value = '';
};

const sauvegarderFautes = () => {
    if (currentProcedureMilitaireId.value === null) return;
    
    sauvegardeFautesEnCours.value = true;
    
    router.patch(route('procedure.militaire.fautes.update', {
        procedure: props.procedureId,
        procedureMilitaire: currentProcedureMilitaireId.value
    }), { 
        fautes_militaires: fautesSelectionnees.value.map(id => parseInt(id))
    }, {
        onSuccess: () => {
            sauvegardeFautesEnCours.value = false;
            fermerGestionFautes();
            emit('updated');
        },
        onError: () => {
            sauvegardeFautesEnCours.value = false;
        }
    });
};

// ====== FONCTIONS POUR LES CATÉGORIES ET FAUTES ======
const getCategorieLibelle = (id) => {
    const cat = categoriesFautes.value.find(c => c.id == id);
    return cat ? cat.libelle : 'Catégorie inconnue';
};

const getFautesByCategorie = (categorieId) => {
    const cat = categoriesFautes.value.find(c => c.id == categorieId);
    return cat ? cat.fautes || [] : [];
};

// ====== CRUD CATÉGORIE ======
const ouvrirModalCreationCategorie = () => {
    categorieEditId.value = null;
    categorieForm.value = { libelle: '', description: '' };
    showCategorieModal.value = true;
};

const ouvrirModalEditionCategorie = (categorie) => {
    if (!categorie) return;
    categorieEditId.value = categorie.id;
    categorieForm.value = { libelle: categorie.libelle, description: categorie.description || '' };
    showCategorieModal.value = true;
};

const fermerModalCategorie = () => {
    showCategorieModal.value = false;
    categorieEditId.value = null;
    categorieForm.value = { libelle: '', description: '' };
    categorieEnCours.value = false;
};

const sauvegarderCategorie = async () => {
    if (!categorieForm.value.libelle.trim()) return;
    
    categorieEnCours.value = true;
    try {
        const url = categorieEditId.value 
            ? `/fautes/categories/${categorieEditId.value}`
            : '/fautes/categories';
        const method = categorieEditId.value ? 'PATCH' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(categorieForm.value)
        });
        
        if (!response.ok) throw new Error('Erreur lors de la sauvegarde');
        
        await loadCategoriesFautes();
        fermerModalCategorie();
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la sauvegarde de la catégorie');
    } finally {
        categorieEnCours.value = false;
    }
};

const supprimerCategorie = async (id) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) return;
    
    try {
        const response = await fetch(`/fautes/categories/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        if (!response.ok) throw new Error('Erreur lors de la suppression');
        
        await loadCategoriesFautes();
        if (categorieSelectionnee.value == id) {
            categorieSelectionnee.value = '';
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de la catégorie');
    }
};

// ====== CRUD FAUTE ======
const ouvrirModalCreationFaute = () => {
    if (!categorieSelectionnee.value) {
        alert('Veuillez d\'abord sélectionner une catégorie');
        return;
    }
    fauteEditId.value = null;
    fauteForm.value = { 
        categorie_faute_id: parseInt(categorieSelectionnee.value), 
        libelle: '', 
        code: '', 
        description: '' 
    };
    showFauteModal.value = true;
};

const ouvrirModalEditionFaute = (faute) => {
    if (!faute) return;
    fauteEditId.value = faute.id;
    fauteForm.value = {
        categorie_faute_id: faute.categorie_faute_id,
        libelle: faute.libelle,
        code: faute.code || '',
        description: faute.description || ''
    };
    showFauteModal.value = true;
};

const fermerModalFaute = () => {
    showFauteModal.value = false;
    fauteEditId.value = null;
    fauteForm.value = { categorie_faute_id: '', libelle: '', code: '', description: '' };
    fauteEnCours.value = false;
};

const sauvegarderFaute = async () => {
    if (!fauteForm.value.categorie_faute_id || !fauteForm.value.libelle.trim()) return;
    
    fauteEnCours.value = true;
    try {
        const url = fauteEditId.value 
            ? `/fautes/fautes/${fauteEditId.value}`
            : '/fautes/fautes';
        const method = fauteEditId.value ? 'PATCH' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(fauteForm.value)
        });
        
        if (!response.ok) throw new Error('Erreur lors de la sauvegarde');
        
        await loadCategoriesFautes();
        fermerModalFaute();
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la sauvegarde de la faute');
    } finally {
        fauteEnCours.value = false;
    }
};

const supprimerFauteDb = async (id) => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette faute ?')) return;
    
    try {
        const response = await fetch(`/fautes/fautes/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        if (!response.ok) throw new Error('Erreur lors de la suppression');
        
        await loadCategoriesFautes();
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la suppression de la faute');
    }
};

// ====== PARTIES CIVILES ======
const ouvrirEditionPartiesCiviles = () => {
    editPartiesCivilesForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.parties_civiles || []));
    editTemoinsForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.temoins || []));
    editCivileResponsablesForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.civile_responsables || []));
    editGarantsForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.garants || []));
    editAvocatsForm.value = JSON.parse(JSON.stringify(props.procedureMilitaire?.avocats || []));
    showEditPartiesCiviles.value = true;
};

const sauvegarderPartiesCiviles = () => {
    savingPartiesCiviles.value = true;
    router.patch(route('procedure.militaire.parties-civiles.update', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), { 
        parties_civiles: editPartiesCivilesForm.value,
        temoins: editTemoinsForm.value,
        civile_responsables: editCivileResponsablesForm.value,
        garants: editGarantsForm.value,
        avocats: editAvocatsForm.value
    }, {
        onSuccess: () => {
            savingPartiesCiviles.value = false;
            showEditPartiesCiviles.value = false;
            emit('updated');
        },
        onError: () => {
            savingPartiesCiviles.value = false;
        }
    });
};

// ====== SUPPRESSION DU MILITAIRE ======
const confirmDeleteMilitaire = () => {
    showDeleteModal.value = true;
};

const supprimerMilitaire = () => {
    deleting.value = true;
    
    router.delete(route('procedure.militaire.supprimer', {
        procedure: props.procedureId,
        procedureMilitaire: props.procedureMilitaire.id
    }), {
        onSuccess: () => {
            deleting.value = false;
            showDeleteModal.value = false;
            emit('deleted');
            emit('updated');
        },
        onError: () => {
            deleting.value = false;
        },
        preserveScroll: true
    });
};
</script>