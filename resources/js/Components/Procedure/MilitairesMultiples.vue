<template>
    <div class="space-y-4">
        <!-- En-tête avec le toggle plurialité -->
        <div class="flex items-center justify-between bg-gpj-50 dark:bg-gpj-800 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="estPlurielleLocal"
                        class="sr-only peer"
                        @change="onTogglePlurialite"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-gpj-500 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:inset-s-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-gpj-500"></div>
                    <span class="ms-3 text-sm font-medium text-gpj-700 dark:text-gpj-300">
                        {{ estPlurielleLocal ? 'Pluriel' : 'Individuel' }}
                    </span>
                </label>
                <span class="text-xs text-gpj-400">
                    {{ estPlurielleLocal ? 'Plusieurs personnels concernés' : 'Un seul personnel concerné' }}
                </span>
            </div>
            <span class="text-xs text-gpj-500 bg-gpj-100 dark:bg-gpj-700 px-2 py-1 rounded-full">
                {{ militairesLocal.length }} personnel{{ militairesLocal.length > 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Liste des personnels -->
        <div v-if="militairesLocal.length === 0" class="text-center py-8 text-gpj-400 border-2 border-dashed border-gpj-200 rounded-lg">
            <i class="pi pi-user-plus text-3xl block mb-2"></i>
            <p class="text-sm">Aucun personnel ajouté</p>
            <p class="text-xs">Ajoutez un personnel pour commencer</p>
        </div>

        <!-- Cartes des personnels -->
        <div v-for="(militaire, index) in militairesLocal" :key="index" class="border border-gpj-200 dark:border-gpj-700 rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-gpj-500 bg-gpj-100 dark:bg-gpj-700 px-2 py-1 rounded-full">
                        #{{ index + 1 }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full" :class="militaire.type_personnel === 'militaire' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'">
                        {{ militaire.type_personnel === 'militaire' ? 'Militaire' : 'Civil' }}
                    </span>
                    <span v-if="militaire.militaire_id" class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                        <i class="pi pi-check-circle text-xs mr-1"></i> Existant
                    </span>
                    <span v-else-if="militaire.nom && militaire.prenom" class="text-xs text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                        <i class="pi pi-plus-circle text-xs mr-1"></i> Nouveau
                    </span>
                </div>
                <button
                    v-if="militairesLocal.length > 1 && estPlurielleLocal"
                    type="button"
                    @click="supprimerMilitaire(index)"
                    class="text-red-400 hover:text-red-600 transition-colors"
                    title="Supprimer ce personnel"
                >
                    <i class="pi pi-trash"></i>
                </button>
            </div>

            <!-- Type de personnel -->
            <div class="mb-3">
                <label class="block text-xs font-medium text-gpj-600 mb-1">Type de personnel</label>
                <select 
                    v-model="militaire.type_personnel" 
                    @change="onTypePersonnelChange(index)"
                    class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                    :disabled="!!militaire.militaire_id"
                >
                    <option value="militaire">Militaire</option>
                    <option value="civil">Civil</option>
                </select>
            </div>

            <!-- Recherche/sélection du personnel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="relative">
                    <label class="block text-xs font-medium text-gpj-600 mb-1">Rechercher un personnel existant</label>
                    <SearchSelect
                        :options="optionsMilitaires"
                        v-model="militaire.militaire_id"
                        placeholder="Rechercher par nom, matricule..."
                        @search="(query) => rechercherMilitaires(query, index)"
                        @change="onMilitaireChange(index)"
                    />
                </div>

                <div class="border-t md:border-t-0 md:border-l border-gpj-200 dark:border-gpj-700 md:pl-3 pt-3 md:pt-0">
                    <p class="text-xs font-medium text-gpj-500 mb-2">OU créer un nouveau personnel</p>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input
                                v-model="militaire.nom"
                                type="text"
                                placeholder="Nom *"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div>
                            <input
                                v-model="militaire.prenom"
                                type="text"
                                placeholder="Prénom *"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div v-if="militaire.type_personnel === 'militaire'">
                            <select v-model="militaire.grade_id" :disabled="!!militaire.militaire_id"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500 disabled:opacity-50">
                                <option value="">Grade</option>
                                <option v-for="grade in grades" :key="grade.id" :value="grade.id">
                                    {{ grade.libelle }}
                                </option>
                            </select>
                        </div>
                        <div v-else>
                            <input
                                v-model="militaire.profession"
                                type="text"
                                placeholder="Profession"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                        <div>
                            <input
                                v-model="militaire.matricule"
                                type="text"
                                placeholder="Matricule"
                                class="w-full rounded-lg border border-gpj-200 text-sm py-1.5 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500"
                                :disabled="!!militaire.militaire_id"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Infractions -->
            <div class="mt-4 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-medium text-gpj-600">
                            <i class="pi pi-list mr-1"></i> Infractions
                        </label>
                        <span class="text-xs text-gpj-400">{{ (militaire.infractions || []).length }} sélectionnée(s)</span>
                    </div>
                    <button
                        type="button"
                        @click="ouvrirModalCreationInfraction(index)"
                        class="px-3 py-1.5 bg-gpj-500 text-white text-xs font-medium rounded-lg hover:bg-gpj-600 transition-colors flex items-center gap-1"
                    >
                        <i class="pi pi-plus text-xs"></i> Créer
                    </button>
                </div>
                <div class="flex flex-wrap gap-2">
                    <label
                        v-for="inf in infractions"
                        :key="inf.id"
                        class="flex items-center gap-1.5 text-xs cursor-pointer px-2 py-1 rounded border border-gpj-200 hover:bg-gpj-50 transition-colors"
                        :class="{
                            'bg-gpj-50 border-gpj-400': (militaire.infractions || []).includes(inf.id)
                        }"
                    >
                        <input
                            type="checkbox"
                            :value="inf.id"
                            v-model="militaire.infractions"
                            class="rounded border-gpj-300 text-gpj-500 focus:ring-gpj-500"
                        />
                        <span>{{ inf.libelle }}</span>
                        <span class="text-gpj-400 text-[10px]">{{ inf.code_infraction }}</span>
                    </label>
                </div>
            </div>

            <!-- Fautes militaires -->
            <div v-if="militaire.type_personnel === 'militaire'" class="mt-3 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-exclamation-triangle mr-1"></i> Fautes militaires
                    </label>
                    <button
                        type="button"
                        @click="ouvrirGestionFautes(index)"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-cog mr-1"></i> Gérer
                    </button>
                </div>
                <div v-if="(militaire.fautes_militaires || []).length === 0" class="text-xs text-gpj-400 py-1">
                    Aucune faute
                </div>
                <div v-for="(fauteId, fi) in militaire.fautes_militaires" :key="fi" class="flex items-center gap-2 mb-1 p-1 bg-gpj-50 rounded">
                    <span class="text-xs text-gpj-700 flex-1">
                        {{ getFauteLibelle(fauteId) }}
                    </span>
                    <button
                        type="button"
                        @click="retirerFauteMilitaire(index, fi)"
                        class="text-red-400 hover:text-red-600 text-xs"
                    >
                        <i class="pi pi-times"></i>
                    </button>
                </div>
            </div>

            <!-- Parties civiles -->
            <div class="mt-3 border-t border-gpj-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-medium text-gpj-600">
                        <i class="pi pi-users mr-1"></i> Parties civiles
                    </label>
                    <button
                        type="button"
                        @click="ajouterPartieCivile(index)"
                        class="text-xs text-gpj-500 hover:text-gpj-700"
                    >
                        <i class="pi pi-plus-circle mr-1"></i> Ajouter
                    </button>
                </div>
                <div v-if="(militaire.parties_civiles || []).length === 0" class="text-xs text-gpj-400 py-1">
                    Aucune partie civile
                </div>
                <div v-for="(pc, pi) in militaire.parties_civiles" :key="pi" class="grid grid-cols-3 gap-2 mb-2 p-2 bg-gpj-50 dark:bg-gpj-800 rounded">
                    <select v-model="pc.type" class="rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500">
                        <option value="Personne">Personne</option>
                        <option value="Structure">Structure</option>
                    </select>
                    <input v-model="pc.nom" placeholder="Nom *" class="rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                    <div class="flex items-center gap-2">
                        <input v-if="pc.type === 'Personne'" v-model="pc.prenom" placeholder="Prénom" class="flex-1 rounded border border-gpj-200 text-xs py-1 px-2 focus:outline-none focus:ring-2 focus:ring-gpj-500" />
                        <button
                            type="button"
                            @click="supprimerPartieCivile(index, pi)"
                            class="text-red-400 hover:text-red-600 text-xs shrink-0"
                        >
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton ajouter -->
        <button
            v-if="estPlurielleLocal"
            type="button"
            @click="ajouterMilitaire"
            class="w-full py-3 border-2 border-dashed border-gpj-300 rounded-lg text-sm text-gpj-500 hover:border-gpj-500 hover:text-gpj-600 hover:bg-gpj-50 transition-colors"
        >
            <i class="pi pi-plus mr-2"></i>
            Ajouter un autre personnel
        </button>

        <!-- Modale création infraction -->
        <div v-if="showCreateInfractionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <!-- ... contenu existant ... -->
        </div>

        <!-- ============================================================ -->
        <!-- MODALE GESTION DES FAUTES AVEC CRUD COMPLET                   -->
        <!-- ============================================================ -->
        <div v-if="showGestionFautesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white dark:bg-gpj-900 rounded-xl p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gpj-800 dark:text-white">Gestion des fautes militaires</h3>
                        <p class="text-sm text-gpj-500">Gérez les catégories et les fautes</p>
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
                            <button
                                @click="ouvrirModalCreationCategorie"
                                class="px-3 py-1.5 bg-emerald-500 text-white text-xs font-medium rounded-lg hover:bg-emerald-600 transition-colors flex items-center gap-1"
                            >
                                <i class="pi pi-plus text-xs"></i> Nouvelle
                            </button>
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
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import SearchSelect from '@/Components/GPJ/SearchSelect.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    estPlurielle: {
        type: Boolean,
        default: false
    },
    infractions: {
        type: Array,
        default: () => []
    },
    militairesOptions: {
        type: Array,
        default: () => []
    },
    grades: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue', 'update:estPlurielle', 'change', 'infraction-created']);

// ====== ÉTATS ======
const militairesLocal = ref(props.modelValue || []);
const estPlurielleLocal = ref(props.estPlurielle);
const optionsMilitaires = ref(props.militairesOptions || []);
const grades = ref(props.grades || []);
const showCreateInfractionModal = ref(false);
const creerInfractionEnCours = ref(false);
const currentMilitaireIndex = ref(null);

// ====== ÉTATS POUR LES FAUTES ======
const showGestionFautesModal = ref(false);
const sauvegardeFautesEnCours = ref(false);
const currentFautesIndex = ref(null);
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

// ====== NOUVELLE INFRACTION ======
const newInfraction = ref({
    libelle: '',
    code_infraction: '',
    classification: '',
    nature: '',
    description: ''
});

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

// ====== INITIALISATION ======
if (militairesLocal.value.length === 0) {
    militairesLocal.value.push({
        type_personnel: 'militaire',
        militaire_id: null,
        nom: '',
        prenom: '',
        profession: '',
        grade: '',
        grade_id: '',
        matricule: '',
        infractions: [],
        fautes_militaires: [],
        parties_civiles: [],
        est_nouveau: true
    });
}

// Charger les catégories au montage
loadCategoriesFautes();

// ====== WATCHERS ======
watch(estPlurielleLocal, (newVal) => {
    if (!newVal && militairesLocal.value.length > 1) {
        const first = militairesLocal.value[0];
        militairesLocal.value = [first];
        emitChange();
    }
    emit('update:estPlurielle', newVal);
});

watch(() => props.modelValue, (newVal) => {
    if (JSON.stringify(newVal) !== JSON.stringify(militairesLocal.value)) {
        militairesLocal.value = newVal || [];
    }
}, { deep: true });

watch(() => props.estPlurielle, (newVal) => {
    estPlurielleLocal.value = newVal;
});

watch(militairesLocal, () => {
    emitChange();
}, { deep: true });

watch(() => props.grades, (newVal) => {
    grades.value = newVal || [];
}, { deep: true });

// ====== FONCTIONS ======
const emitChange = () => {
    emit('update:modelValue', militairesLocal.value);
    emit('change', militairesLocal.value);
};

// ====== GESTION DU TYPE DE PERSONNEL ======
const onTypePersonnelChange = (index) => {
    const mil = militairesLocal.value[index];
    if (mil.type_personnel === 'civil') {
        mil.grade_id = '';
        mil.matricule = '';
        mil.grade = '';
    }
    emitChange();
};

// ====== GESTION DES MILITAIRES ======
const ajouterMilitaire = () => {
    if (!estPlurielleLocal.value) return;
    militairesLocal.value.push({
        type_personnel: 'militaire',
        militaire_id: null,
        nom: '',
        prenom: '',
        profession: '',
        grade: '',
        grade_id: '',
        matricule: '',
        infractions: [],
        fautes_militaires: [],
        parties_civiles: [],
        est_nouveau: true
    });
    emitChange();
};

const supprimerMilitaire = (index) => {
    if (militairesLocal.value.length > 1 && estPlurielleLocal.value) {
        militairesLocal.value.splice(index, 1);
        emitChange();
    }
};

const rechercherMilitaires = async (query, index) => {
    if (!query || query.length < 2) {
        optionsMilitaires.value = props.militairesOptions || [];
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

const onMilitaireChange = (index) => {
    const mil = militairesLocal.value[index];
    if (mil.militaire_id) {
        const selected = optionsMilitaires.value.find(m => m.value === mil.militaire_id);
        if (selected) {
            mil.nom = selected.label.split(' ')[0] || '';
            mil.prenom = selected.label.split(' ').slice(1).join(' ') || '';
            mil.matricule = selected.sublabel || '';
            mil.type_personnel = selected.type || 'militaire';
        }
        mil.est_nouveau = false;
    } else {
        mil.est_nouveau = true;
    }
    emitChange();
};

const onTogglePlurialite = () => {
    estPlurielleLocal.value = !estPlurielleLocal.value;
};

// ====== GESTION DES FAUTES (pour le militaire) ======
const getFauteLibelle = (id) => {
    return fautesMap.value[id] || 'Faute #' + id;
};

const ouvrirGestionFautes = (index) => {
    currentFautesIndex.value = index;
    const mil = militairesLocal.value[index];
    fautesSelectionnees.value = [...(mil.fautes_militaires || [])];
    showGestionFautesModal.value = true;
    loadCategoriesFautes();
};

const fermerGestionFautes = () => {
    showGestionFautesModal.value = false;
    currentFautesIndex.value = null;
    fautesSelectionnees.value = [];
    categorieSelectionnee.value = '';
};

const sauvegarderFautes = () => {
    if (currentFautesIndex.value === null) return;
    
    sauvegardeFautesEnCours.value = true;
    const mil = militairesLocal.value[currentFautesIndex.value];
    mil.fautes_militaires = fautesSelectionnees.value.map(id => parseInt(id));
    emitChange();
    fermerGestionFautes();
    sauvegardeFautesEnCours.value = false;
};

// ====== SUPPRIMER UNE FAUTE DE LA LISTE DU MILITAIRE ======
const retirerFauteMilitaire = (index, fauteIndex) => {
    militairesLocal.value[index].fautes_militaires.splice(fauteIndex, 1);
    emitChange();
};

// ====== FONCTIONS POUR LES CATÉGORIES ET FAUTES ======
const getCategorieSelectionnee = () => {
    return categoriesFautes.value.find(c => c.id == categorieSelectionnee.value);
};

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

// ====== GESTION DES PARTIES CIVILES ======
const ajouterPartieCivile = (index) => {
    if (!militairesLocal.value[index].parties_civiles) {
        militairesLocal.value[index].parties_civiles = [];
    }
    militairesLocal.value[index].parties_civiles.push({ 
        type: 'Personne', 
        nom: '', 
        prenom: '', 
        profession: '', 
        adresse: '' 
    });
    emitChange();
};

const supprimerPartieCivile = (index, pcIndex) => {
    militairesLocal.value[index].parties_civiles.splice(pcIndex, 1);
    emitChange();
};

// ====== CRÉATION D'INFRACTION ======
const ouvrirModalCreationInfraction = (index) => {
    currentMilitaireIndex.value = index;
    newInfraction.value = {
        libelle: '',
        code_infraction: '',
        classification: '',
        nature: '',
        description: ''
    };
    showCreateInfractionModal.value = true;
};

const fermerModalCreationInfraction = () => {
    showCreateInfractionModal.value = false;
    currentMilitaireIndex.value = null;
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

    creerInfractionEnCours.value = true;

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
            throw new Error('Erreur lors de la création');
        }

        const data = await response.json();
        
        emit('infraction-created', data);
        
        const milIndex = currentMilitaireIndex.value;
        if (milIndex !== null && militairesLocal.value[milIndex]) {
            if (!militairesLocal.value[milIndex].infractions) {
                militairesLocal.value[milIndex].infractions = [];
            }
            militairesLocal.value[milIndex].infractions.push(data.id);
            emitChange();
        }
        
        fermerModalCreationInfraction();

    } catch (error) {
        console.error('Erreur création infraction:', error);
        alert('Erreur lors de la création : ' + error.message);
    } finally {
        creerInfractionEnCours.value = false;
    }
};
</script>