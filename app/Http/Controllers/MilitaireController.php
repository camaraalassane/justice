<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Models\CategorieGrade;
use App\Models\Grade;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MilitaireController extends Controller
{
    use LogsActivity;

    /**
     * Liste des militaires avec filtres
     */
    public function index(Request $request)
    {
        $militaires = Militaire::with(['grade:id,libelle,abreviation'])
            ->when($request->search, function ($query, $search) {
                return $query->recherche($search);
            })
            ->when($request->statut, fn($q) => $q->where('statut', $request->statut))
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Militaires/Index', [
            'militaires' => $militaires,
            'filters' => $request->only(['search', 'statut']),
        ]);
    }

    /**
     * Formulaire de création d'un militaire
     */
    public function create()
    {
        return Inertia::render('Militaires/Create', [
            'categoriesGrades' => CategorieGrade::with('grades')->orderBy('ordre')->get(),
            'armees' => [
                'Armée de Terre',
                'Armée de l\'Air',
                'Garde Nationale',
                'Gendarmerie Nationale',
                'Police Nationale',
                'Protection Civile',
                'Direction du Génie Militaire',
                'Direction du Service de Santé des Armées',
                'Direction du Matériel',
                'Direction des Transmissions',
                'État-Major Général',
                'Autre',
            ],
        ]);
    }

    /**
     * Enregistrer un nouveau militaire
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:50|unique:militaires,matricule',
            'date_naissance' => 'nullable|date',
            'grade_id' => 'nullable|exists:grades,id',
            'unite' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'statut' => 'nullable|in:Actif,Suspendu,Déserteur,Radié',
            'genre' => 'nullable|in:Masculin,Féminin',
            'armee' => 'nullable|string|max:255',
        ]);

        // Définir des valeurs par défaut pour les champs optionnels
        $validated['statut'] = $validated['statut'] ?? 'Actif';
        $validated['matricule'] = $validated['matricule'] ?? $this->genererMatricule();

        // S'assurer que les champs null sont bien null (et non des chaînes vides)
        $fields = ['date_naissance', 'grade_id', 'unite', 'adresse', 'telephone', 'genre', 'armee'];
        foreach ($fields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $militaire = Militaire::create($validated);
        $this->logCreate($militaire, "Militaire créé : {$militaire->matricule} - {$militaire->nom} {$militaire->prenoms}");

        return redirect()->route('militaires.index')->with('success', 'Militaire créé avec succès.');
    }

    /**
     * Générer un matricule automatique
     */
    private function genererMatricule(): string
    {
        $annee = now()->format('Y');
        $dernier = Militaire::whereYear('created_at', $annee)
            ->orderBy('id', 'desc')
            ->first();

        if ($dernier && $dernier->matricule) {
            $parts = explode('-', $dernier->matricule);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }

        return 'MIL-' . $annee . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Fiche détaillée d'un militaire
     */
    public function show(Militaire $militaire)
    {
        $militaire->load([
            'grade.categorie',
            'procedures' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'procedures.infractions',
            'procedures.jugement',
        ]);

        return Inertia::render('Militaires/Show', [
            'militaire' => $militaire,
        ]);
    }

    /**
     * Formulaire de modification d'un militaire
     */
    public function edit(Militaire $militaire)
    {
        return Inertia::render('Militaires/Edit', [
            'militaire' => $militaire->load('grade'),
            'categoriesGrades' => CategorieGrade::with('grades')->orderBy('ordre')->get(),
            'armees' => [
                'Armée de Terre',
                'Armée de l\'Air',
                'Garde Nationale',
                'Gendarmerie Nationale',
                'Police Nationale',
                'Protection Civile',
                'Direction du Génie Militaire',
                'Direction du Service de Santé des Armées',
                'Direction du Matériel',
                'Direction des Transmissions',
                'État-Major Général',
                'Autre',
            ],
        ]);
    }

    /**
     * Mettre à jour un militaire
     */
    public function update(Request $request, Militaire $militaire)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:50|unique:militaires,matricule,' . $militaire->id,
            'date_naissance' => 'nullable|date',
            'grade_id' => 'nullable|exists:grades,id',
            'unite' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'statut' => 'nullable|in:Actif,Suspendu,Déserteur,Radié',
            'genre' => 'nullable|in:Masculin,Féminin',
            'armee' => 'nullable|string|max:255',
        ]);

        // Conserver l'ancien matricule si aucun nouveau n'est fourni
        if (empty($validated['matricule'])) {
            $validated['matricule'] = $militaire->matricule;
        }

        // S'assurer que les champs null sont bien null
        $fields = ['date_naissance', 'grade_id', 'unite', 'adresse', 'telephone', 'genre', 'armee'];
        foreach ($fields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $changes = array_diff_assoc($validated, $militaire->only(array_keys($validated)));
        $militaire->update($validated);

        if (!empty($changes)) {
            $this->logUpdate($militaire, "Militaire modifié : {$militaire->matricule} - {$militaire->nom} {$militaire->prenoms}", $changes);
        }

        return redirect()->route('militaires.index')->with('success', 'Militaire modifié avec succès.');
    }

    /**
     * Supprimer un militaire
     */
    public function destroy(Militaire $militaire)
    {
        if (auth()->user()->role !== 'SD') {
            return back()->with('error', 'Seul le Sous-Directeur peut supprimer un militaire.');
        }

        if ($militaire->procedures()->exists()) {
            return back()->with('error', 'Impossible de supprimer un militaire qui a des procédures judiciaires.');
        }

        $this->logDelete($militaire, "Militaire supprimé : {$militaire->matricule} - {$militaire->nom} {$militaire->prenoms}");
        $militaire->delete();

        return redirect()->route('militaires.index')->with('success', 'Militaire supprimé avec succès.');
    }

/**
 * Génération du casier judiciaire PDF
 */
public function imprimerCasier(Militaire $militaire)
{
    // Charger le militaire avec ses relations
    $militaire->load([
        'grade',
        'procedures' => function ($q) {
            $q->orderBy('created_at', 'desc');
        },
        'procedures.infractions',
        'procedures.jugement',
        'procedures.procedureMilitaires.militaire',
        'procedureMilitaires.militaire',
        'procedureMilitaires.procedure.infractions',
        'procedureMilitaires.procedure.jugement',
    ]);

    // Récupérer toutes les procédures liées à ce militaire
    // via la relation directe ET via procedure_militaire
    $allProcedures = collect();

    // 1. Procédures où il est le militaire principal (militaire_id)
    $proceduresPrincipal = $militaire->procedures->map(function($p) {
        $p->est_principal = true;
        // Récupérer les infractions du pivot pour ce militaire
        $pivot = $p->procedureMilitaires->where('militaire_id', $p->militaire_id)->first();
        $p->infractions_pivot = $pivot ? $pivot->infractions : [];
        return $p;
    });

    // 2. Procédures via la table procedure_militaire
    $proceduresViaPivot = $militaire->procedureMilitaires
        ->map(function($pm) {
            $p = $pm->procedure;
            if ($p) {
                $p->est_principal = false;
                $p->pivot_data = $pm;
                $p->infractions_pivot = $pm->infractions ?? [];
                return $p;
            }
            return null;
        })
        ->filter();

    // Fusionner et supprimer les doublons
    $allProcedures = $proceduresPrincipal->merge($proceduresViaPivot)
        ->unique('id')
        ->sortByDesc('created_at');

    $pdf = Pdf::loadView('pdf.casier_judiciaire', [
        'militaire' => $militaire,
        'procedures' => $allProcedures,
        'date_edition' => now()->format('d/m/Y à H:i'),
    ]);

    $pdf->setPaper('a4', 'portrait');

    return $pdf->stream("casier-judiciaire-{$militaire->matricule}.pdf");
}

    /**
     * Recherche AJAX de militaires pour autocomplétion
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $termes = explode(' ', trim($query));

        $militaires = Militaire::select('id', 'matricule', 'nom', 'prenoms', 'grade_id')
            ->with('grade:id,libelle,abreviation')
            ->where(function ($q) use ($termes) {
                foreach ($termes as $terme) {
                    $terme = trim($terme);
                    if (strlen($terme) >= 2) {
                        $q->where(function ($subQ) use ($terme) {
                            $subQ->where('nom', 'ILIKE', "%{$terme}%")
                                 ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                                 ->orWhere('matricule', 'ILIKE', "%{$terme}%");
                        });
                    }
                }
            })
            ->orderBy('nom')
            ->limit(20)
            ->get()
            ->map(function ($m) {
                return [
                    'value' => $m->id,
                    'label' => "{$m->nom} {$m->prenoms}",
                    'sublabel' => "{$m->matricule} - " . ($m->grade->libelle ?? 'N/A'),
                ];
            });

        return response()->json($militaires);
    }
}