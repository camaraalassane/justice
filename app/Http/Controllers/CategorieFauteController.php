<?php

namespace App\Http\Controllers;

use App\Models\CategorieFaute;
use App\Models\FauteMilitaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategorieFauteController extends Controller
{
    // ==================== API ====================

    /**
     * Récupérer toutes les catégories avec leurs fautes (pour API)
     */
    public function getCategories()
    {
        try {
            Log::info('=== getCategories appelé ===');
            
            $categories = CategorieFaute::with(['fautes' => function($query) {
                $query->orderBy('ordre')->orderBy('libelle');
            }])
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->get();

            Log::info('Catégories trouvées: ' . $categories->count());
            
            return response()->json($categories);
        } catch (\Exception $e) {
            Log::error('Erreur getCategories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des catégories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les fautes d'une catégorie
     */
    public function getFautesByCategorie(CategorieFaute $categorie)
    {
        try {
            $fautes = $categorie->fautes()->orderBy('ordre')->orderBy('libelle')->get();
            return response()->json($fautes);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des fautes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistrer une nouvelle catégorie
     */
    public function storeCategorie(Request $request)
    {
        try {
            Log::info('=== STORE CATEGORIE ===');
            Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'libelle' => 'required|string|max:255|unique:categorie_fautes,libelle',
                'description' => 'nullable|string',
            ]);

            Log::info('Validation réussie:', $validated);

            $validated['ordre'] = CategorieFaute::max('ordre') + 1;

            $categorie = CategorieFaute::create($validated);

            Log::info('Catégorie créée avec succès ID: ' . $categorie->id);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie créée avec succès',
                'data' => $categorie
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation catégorie:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création catégorie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une catégorie
     */
    public function updateCategorie(Request $request, CategorieFaute $categorie)
    {
        try {
            Log::info('=== UPDATE CATEGORIE ===');
            Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'libelle' => 'required|string|max:255|unique:categorie_fautes,libelle,' . $categorie->id,
                'description' => 'nullable|string',
            ]);

            $categorie->update($validated);

            Log::info('Catégorie mise à jour ID: ' . $categorie->id);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie mise à jour avec succès',
                'data' => $categorie
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation mise à jour catégorie:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour catégorie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function destroyCategorie(CategorieFaute $categorie)
    {
        try {
            Log::info('=== DELETE CATEGORIE ===');
            Log::info('Catégorie ID: ' . $categorie->id);

            if ($categorie->fautes()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer une catégorie qui contient des fautes.'
                ], 400);
            }

            $categorie->delete();

            Log::info('Catégorie supprimée ID: ' . $categorie->id);

            return response()->json([
                'success' => true,
                'message' => 'Catégorie supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression catégorie: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== GESTION DES FAUTES ====================

    /**
     * Enregistrer une nouvelle faute
     */
    public function storeFaute(Request $request)
    {
        try {
            Log::info('=== STORE FAUTE ===');
            Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'categorie_faute_id' => 'required|exists:categorie_fautes,id',
                'libelle' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'description' => 'nullable|string',
            ]);

            Log::info('Validation réussie:', $validated);

            $validated['ordre'] = FauteMilitaire::where('categorie_faute_id', $validated['categorie_faute_id'])->max('ordre') + 1;

            $faute = FauteMilitaire::create($validated);

            Log::info('Faute créée avec succès ID: ' . $faute->id);

            return response()->json([
                'success' => true,
                'message' => 'Faute créée avec succès',
                'data' => $faute
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation faute:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création faute: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la faute: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une faute
     */
    public function updateFaute(Request $request, FauteMilitaire $faute)
    {
        try {
            Log::info('=== UPDATE FAUTE ===');
            Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'categorie_faute_id' => 'required|exists:categorie_fautes,id',
                'libelle' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'description' => 'nullable|string',
            ]);

            $faute->update($validated);

            Log::info('Faute mise à jour ID: ' . $faute->id);

            return response()->json([
                'success' => true,
                'message' => 'Faute mise à jour avec succès',
                'data' => $faute
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation mise à jour faute:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour faute: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la faute: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une faute
     */
    public function destroyFaute(FauteMilitaire $faute)
    {
        try {
            Log::info('=== DELETE FAUTE ===');
            Log::info('Faute ID: ' . $faute->id);

            $faute->delete();

            Log::info('Faute supprimée ID: ' . $faute->id);

            return response()->json([
                'success' => true,
                'message' => 'Faute supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur suppression faute: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la faute: ' . $e->getMessage()
            ], 500);
        }
    }
}