<?php

namespace App\Http\Controllers;

use App\Models\Militaire;
use App\Models\CategorieGrade;
use App\Models\Grade;
use App\Models\Armee;
use App\Models\InfractionBase;
use App\Models\Procedure;
use App\Models\ProcedureMilitaire;
use App\Models\PhaseType;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MilitaireController extends Controller
{
    use LogsActivity;

    /**
     * Liste des personnels avec filtres
     */
    public function index(Request $request)
    {
        $query = Militaire::with(['grade:id,libelle,abreviation', 'armeeRelation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'ILIKE', "%{$search}%")
                  ->orWhere('prenoms', 'ILIKE', "%{$search}%")
                  ->orWhere('matricule', 'ILIKE', "%{$search}%")
                  ->orWhere('unite', 'ILIKE', "%{$search}%")
                  ->orWhere('profession', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('type_personnel')) {
            $query->where('type_personnel', $request->type_personnel);
        }

        $militaires = $query->orderBy('nom')
            ->orderBy('prenoms')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Militaires/Index', [
            'militaires' => $militaires,
            'filters' => $request->only(['search', 'statut', 'type_personnel']),
        ]);
    }

    /**
     * Formulaire de création d'un personnel
     */
    public function create()
    {
        $armees = Armee::orderBy('nom')->get();
        $grades = Grade::orderBy('libelle')->get();

        return Inertia::render('Militaires/Create', [
            'categoriesGrades' => CategorieGrade::with('grades')->orderBy('ordre')->get(),
            'armees' => $armees,
            'grades' => $grades,
            'typePersonnelOptions' => [
                ['value' => 'militaire', 'label' => 'Militaire'],
                ['value' => 'civil', 'label' => 'Civil'],
            ],
        ]);
    }

    /**
     * Enregistrer un nouveau personnel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_personnel' => 'required|in:militaire,civil',
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'profession' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:50|unique:militaires,matricule',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nom_pere' => 'nullable|string|max:255',
            'prenoms_pere' => 'nullable|string|max:255',
            'nom_mere' => 'nullable|string|max:255',
            'prenoms_mere' => 'nullable|string|max:255',
            'grade_id' => 'nullable|exists:grades,id',
            'unite' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
            'telephone' => 'nullable|string|max:20',
            'statut' => 'nullable|string',
            'genre' => 'nullable|in:Masculin,Féminin',
            'armee_id' => 'nullable|exists:armees,id',
            'nouvelle_armee' => 'nullable|string|max:255',
        ]);

        // Normaliser le statut
        if (isset($validated['statut'])) {
            $validated['statut'] = $this->normaliserStatut($validated['statut']);
        } else {
            $validated['statut'] = 'En activité';
        }

        // Gestion de la nouvelle armée
        if ($request->filled('nouvelle_armee')) {
            $armee = Armee::firstOrCreate(
                ['nom' => $request->nouvelle_armee],
                ['code' => strtoupper(substr($request->nouvelle_armee, 0, 3))]
            );
            $validated['armee_id'] = $armee->id;
        }

        // Si une armée est sélectionnée, mettre à jour le champ armee
        if (!empty($validated['armee_id'])) {
            $armee = Armee::find($validated['armee_id']);
            if ($armee) {
                $validated['armee'] = $armee->nom;
            }
        }

        // Générer le matricule uniquement pour les militaires
        if ($validated['type_personnel'] === 'militaire' && empty($validated['matricule'])) {
            $validated['matricule'] = $this->genererMatricule();
        }

        // Nettoyer les champs vides
        $fields = ['date_naissance', 'lieu_naissance', 'nom_pere', 'prenoms_pere', 'nom_mere', 'prenoms_mere', 'grade_id', 'unite', 'adresse', 'telephone', 'genre', 'profession'];
        foreach ($fields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        // Pour les civils, on nettoie les champs militaires
        if ($validated['type_personnel'] === 'civil') {
            $validated['matricule'] = null;
            $validated['grade_id'] = null;
            $validated['armee_id'] = null;
            $validated['armee'] = null;
            $validated['unite'] = null;
        }

        $militaire = Militaire::create($validated);
        $this->logCreate($militaire, "Personnel créé : {$militaire->nom} {$militaire->prenoms}");

        return redirect()->route('militaires.index')->with('success', 'Personnel créé avec succès.');
    }

    /**
     * Normaliser le statut
     */
    private function normaliserStatut($statut)
    {
        $statutMap = [
            'Actif' => 'En activité',
            'En activite' => 'En activité',
            'En activité' => 'En activité',
            'Inactif' => 'Non activite',
            'Non activite' => 'Non activite',
            'Non activité' => 'Non activite',
            'Retraité' => 'En retraite',
            'En retraite' => 'En retraite',
            'Radié' => 'Radié',
        ];
        return $statutMap[$statut] ?? 'En activité';
    }

    /**
     * Générer un matricule automatique unique
     */
    private function genererMatricule(): string
    {
        $annee = now()->format('Y');
        $prefix = "MIL-{$annee}-";
        
        $dernier = Militaire::where('matricule', 'LIKE', $prefix . '%')
            ->orderBy('matricule', 'desc')
            ->first();

        if ($dernier) {
            $parts = explode('-', $dernier->matricule);
            $num = intval(end($parts)) + 1;
        } else {
            $num = 1;
        }

        $matricule = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
        
        while (Militaire::where('matricule', $matricule)->exists()) {
            $num++;
            $matricule = $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
        }

        return $matricule;
    }

    /**
     * Fiche détaillée d'un personnel
     */
    public function show(Militaire $militaire)
    {
        $militaire->load([
            'grade.categorie',
            'armeeRelation',
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
     * Formulaire de modification d'un personnel
     */
    public function edit(Militaire $militaire, Request $request)
    {
        $armees = Armee::orderBy('nom')->get();
        $grades = Grade::orderBy('libelle')->get();

        $from = $request->input('from', route('militaires.index'));

        return Inertia::render('Militaires/Edit', [
            'militaire' => $militaire->load(['grade', 'armeeRelation']),
            'categoriesGrades' => CategorieGrade::with('grades')->orderBy('ordre')->get(),
            'armees' => $armees,
            'grades' => $grades,
            'from' => $from,
            'typePersonnelOptions' => [
                ['value' => 'militaire', 'label' => 'Militaire'],
                ['value' => 'civil', 'label' => 'Civil'],
            ],
        ]);
    }

    /**
     * Mettre à jour un personnel - AVEC SYNCHRONISATION DES PROCÉDURES
     */
    public function update(Request $request, Militaire $militaire)
    {
        \Log::info('=== UPDATE MILITAIRE ===');
        \Log::info('ID: ' . $militaire->id);
        \Log::info('Données reçues:', $request->all());

        try {
            $validated = $request->validate([
                'type_personnel' => 'required|in:militaire,civil',
                'nom' => 'required|string|max:255',
                'prenoms' => 'required|string|max:255',
                'profession' => 'nullable|string|max:255',
                'matricule' => 'nullable|string|max:50|unique:militaires,matricule,' . $militaire->id,
                'date_naissance' => 'nullable|date',
                'lieu_naissance' => 'nullable|string|max:255',
                'nom_pere' => 'nullable|string|max:255',
                'prenoms_pere' => 'nullable|string|max:255',
                'nom_mere' => 'nullable|string|max:255',
                'prenoms_mere' => 'nullable|string|max:255',
                'grade_id' => 'nullable|exists:grades,id',
                'unite' => 'nullable|string|max:255',
                'adresse' => 'nullable|string',
                'telephone' => 'nullable|string|max:20',
                'genre' => 'nullable|in:Masculin,Féminin',
                'armee_id' => 'nullable|exists:armees,id',
                'nouvelle_armee' => 'nullable|string|max:255',
                'statut' => 'nullable|string',
            ]);

            // Normaliser le statut
            if (isset($validated['statut'])) {
                $validated['statut'] = $this->normaliserStatut($validated['statut']);
            } else {
                $validated['statut'] = $militaire->statut ?? 'En activité';
            }

            // Gérer le champ armee
            if (!empty($validated['armee_id'])) {
                $armee = Armee::find($validated['armee_id']);
                if ($armee) {
                    $validated['armee'] = $armee->nom;
                }
            } elseif ($request->filled('nouvelle_armee')) {
                $armee = Armee::firstOrCreate(
                    ['nom' => $request->nouvelle_armee],
                    ['code' => strtoupper(substr($request->nouvelle_armee, 0, 3))]
                );
                $validated['armee_id'] = $armee->id;
                $validated['armee'] = $armee->nom;
            } else {
                $validated['armee'] = null;
            }

            \Log::info('Validation réussie:', $validated);

            // Conserver l'ancien matricule si aucun nouveau n'est fourni
            if (empty($validated['matricule'])) {
                $validated['matricule'] = $militaire->matricule;
            }

            // Nettoyer les champs vides
            $fields = ['date_naissance', 'lieu_naissance', 'nom_pere', 'prenoms_pere', 'nom_mere', 'prenoms_mere', 'grade_id', 'unite', 'adresse', 'telephone', 'genre', 'profession'];
            foreach ($fields as $field) {
                if (isset($validated[$field]) && $validated[$field] === '') {
                    $validated[$field] = null;
                }
            }

            // Pour les civils, on nettoie les champs militaires
            if ($validated['type_personnel'] === 'civil') {
                $validated['matricule'] = null;
                $validated['grade_id'] = null;
                $validated['armee_id'] = null;
                $validated['armee'] = null;
                $validated['unite'] = null;
            }

            // Mettre à jour le militaire
            $militaire->update($validated);
            $militaire->refresh();

            \Log::info('Militaire mis à jour avec succès');
            \Log::info('Nouvelles données:', $militaire->toArray());

            // ================================================================
            // SYNCHRONISER AVEC LES PROCÉDURES
            // ================================================================
            $this->syncMilitaireWithProcedures($militaire);

            // Log de l'activité
            $this->logUpdate($militaire, "Personnel modifié : {$militaire->nom} {$militaire->prenoms}");

            $from = $request->input('from', route('militaires.index'));

            return redirect()->to($from)->with('success', 'Personnel modifié avec succès. Les procédures associées ont été mises à jour.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Erreur de validation:', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Synchroniser un militaire avec toutes ses procédures
     */
    private function syncMilitaireWithProcedures(Militaire $militaire)
    {
        \Log::info('=== SYNC MILITAIRE WITH PROCEDURES ===');
        \Log::info('Militaire ID: ' . $militaire->id);

        // Récupérer le libellé du grade
        $gradeLibelle = $militaire->grade?->libelle ?? $militaire->grade;

        // Récupérer toutes les entrées procedure_militaire liées à ce militaire
        $procedureMilitaires = ProcedureMilitaire::where('militaire_id', $militaire->id)->get();

        \Log::info('Procédures trouvées: ' . $procedureMilitaires->count());

        foreach ($procedureMilitaires as $pm) {
            $updated = false;
            $changes = [];

            // Mettre à jour le type_personnel
            if ($pm->type_personnel !== $militaire->type_personnel) {
                $pm->type_personnel = $militaire->type_personnel;
                $updated = true;
                $changes['type_personnel'] = ['ancien' => $pm->getOriginal('type_personnel'), 'nouveau' => $militaire->type_personnel];
            }

            // Mettre à jour nom_temp
            if ($pm->nom_temp !== $militaire->nom) {
                $pm->nom_temp = $militaire->nom;
                $updated = true;
                $changes['nom_temp'] = ['ancien' => $pm->getOriginal('nom_temp'), 'nouveau' => $militaire->nom];
            }

            // Mettre à jour prenom_temp
            if ($pm->prenom_temp !== $militaire->prenoms) {
                $pm->prenom_temp = $militaire->prenoms;
                $updated = true;
                $changes['prenom_temp'] = ['ancien' => $pm->getOriginal('prenom_temp'), 'nouveau' => $militaire->prenoms];
            }

            // Mettre à jour grade_temp
            if ($pm->grade_temp !== $gradeLibelle) {
                $pm->grade_temp = $gradeLibelle;
                $updated = true;
                $changes['grade_temp'] = ['ancien' => $pm->getOriginal('grade_temp'), 'nouveau' => $gradeLibelle];
            }

            // Mettre à jour matricule_temp
            if ($pm->matricule_temp !== $militaire->matricule) {
                $pm->matricule_temp = $militaire->matricule;
                $updated = true;
                $changes['matricule_temp'] = ['ancien' => $pm->getOriginal('matricule_temp'), 'nouveau' => $militaire->matricule];
            }

            // Mettre à jour profession_temp
            if ($pm->profession_temp !== $militaire->profession) {
                $pm->profession_temp = $militaire->profession;
                $updated = true;
                $changes['profession_temp'] = ['ancien' => $pm->getOriginal('profession_temp'), 'nouveau' => $militaire->profession];
            }

            if ($updated) {
                $pm->save();
                \Log::info('ProcedureMilitaire #' . $pm->id . ' mis à jour:', $changes);
            }
        }

        // Mettre à jour la table procedures pour le militaire principal
        // Les données sont chargées dynamiquement depuis la table militaires
        // donc pas besoin de mettre à jour

        return $militaire;
    }

    /**
     * Supprimer un personnel
     */
    public function destroy(Militaire $militaire, Request $request)
    {
        if (auth()->user()->role !== 'ADMIN') {
            return back()->with('error', 'Seul l\'Administrateur peut supprimer un personnel.');
        }

        if ($militaire->procedureMilitaires()->exists() || $militaire->procedures()->exists()) {
            return back()->with('error', 'Impossible de supprimer un personnel qui a des procédures judiciaires.');
        }

        $this->logDelete($militaire, "Personnel supprimé : {$militaire->nom} {$militaire->prenoms}");
        $militaire->delete();

        $from = $request->input('from', route('militaires.index'));

        return redirect()->to($from)->with('success', 'Personnel supprimé avec succès.');
    }

    /**
     * Afficher le casier judiciaire d'un militaire
     */
    public function casier(Militaire $militaire)
    {
        // Charger les relations
        $militaire->load(['grade', 'armeeRelation']);

        // Récupérer les procédures du militaire
        $procedures = Procedure::where(function($query) use ($militaire) {
            $query->where('militaire_id', $militaire->id)
                  ->orWhereHas('procedureMilitaires', function($q) use ($militaire) {
                      $q->where('militaire_id', $militaire->id);
                  });
        })
        ->with([
            'procedureMilitaires' => function($q) use ($militaire) {
                $q->with('militaire');
            },
            'infractions',
            'jugement',
            'parquet',
            'procedurePhases' => function($q) {
                $q->orderBy('ordre', 'asc')->with([
                    'phaseType',
                    'champs'
                ]);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        // Enrichir les procédures
        foreach ($procedures as $procedure) {
            $pm = $procedure->procedureMilitaires->where('militaire_id', $militaire->id)->first();
            
            if ($pm && $pm->infractions) {
                $procedure->infractions_pivot = $pm->infractions;
                $procedure->infractions_pivot_models = InfractionBase::whereIn('id', $pm->infractions)->get();
            } else {
                $procedure->infractions_pivot = [];
                $procedure->infractions_pivot_models = collect();
            }

            $procedure->est_principal = ($procedure->militaire_id == $militaire->id);
            $procedure->est_condamne = false;
            $procedure->peine_principale = null;
            $procedure->peine_description = null;
            $procedure->date_condamnation = null;

            foreach ($procedure->procedurePhases as $phase) {
                if ($phase->phaseType && $phase->phaseType->slug === 'ordre_de_poursuite') {
                    if ($phase->est_condamne || !empty($phase->peine_principale)) {
                        $procedure->est_condamne = true;
                        $procedure->peine_principale = $phase->peine_principale;
                        $procedure->peine_description = $phase->peine_description;
                        $procedure->date_condamnation = $phase->date_phase;
                        break;
                    }
                }
            }

            if ($procedure->jugement) {
                $procedure->est_condamne = $procedure->jugement->verdict === 'Condamnation';
                if ($procedure->est_condamne) {
                    $procedure->peine_principale = $procedure->jugement->peine_principale;
                    $procedure->peine_description = $procedure->jugement->peines_complementaires;
                    $procedure->date_condamnation = $procedure->jugement->date_jugement;
                }
            }
        }

        $proceduresEnCours = $procedures->filter(function($p) {
            $estCloturee = $p->phase === 'Cloturee';
            $aJugement = $p->jugement !== null;
            return !$estCloturee && !$aJugement;
        });

        $condamnations = $procedures->filter(function($p) {
            return $p->est_condamne === true;
        });

        $peutModifier = auth()->user() && in_array(auth()->user()->role, ['ADMIN', 'CDD', 'CDS', 'ADS']);

        return Inertia::render('Militaires/Casier', [
            'militaire' => $militaire,
            'procedures' => $procedures,
            'proceduresEnCours' => $proceduresEnCours->values(),
            'condamnations' => $condamnations->values(),
            'peutModifier' => $peutModifier,
        ]);
    }

    /**
     * Exporter le casier judiciaire en PDF
     */
    public function exportCasierPdf(Militaire $militaire)
    {
        $militaire->load(['grade', 'armeeRelation']);

        $procedures = Procedure::where(function($query) use ($militaire) {
            $query->where('militaire_id', $militaire->id)
                  ->orWhereHas('procedureMilitaires', function($q) use ($militaire) {
                      $q->where('militaire_id', $militaire->id);
                  });
        })
        ->with([
            'procedureMilitaires' => function($q) use ($militaire) {
                $q->with('militaire');
            },
            'infractions',
            'jugement',
            'parquet',
            'procedurePhases' => function($q) {
                $q->orderBy('ordre', 'asc')->with([
                    'phaseType',
                    'champs'
                ]);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        foreach ($procedures as $procedure) {
            $pm = $procedure->procedureMilitaires->where('militaire_id', $militaire->id)->first();
            
            if ($pm && $pm->infractions) {
                $procedure->infractions_pivot = $pm->infractions;
                $procedure->infractions_pivot_models = InfractionBase::whereIn('id', $pm->infractions)->get();
            } else {
                $procedure->infractions_pivot = [];
                $procedure->infractions_pivot_models = collect();
            }

            $procedure->est_principal = ($procedure->militaire_id == $militaire->id);
            $procedure->est_condamne = false;
            $procedure->peine_principale = null;
            $procedure->peine_description = null;
            $procedure->date_condamnation = null;

            foreach ($procedure->procedurePhases as $phase) {
                if ($phase->phaseType && $phase->phaseType->slug === 'ordre_de_poursuite') {
                    if ($phase->est_condamne || !empty($phase->peine_principale)) {
                        $procedure->est_condamne = true;
                        $procedure->peine_principale = $phase->peine_principale;
                        $procedure->peine_description = $phase->peine_description;
                        $procedure->date_condamnation = $phase->date_phase;
                        break;
                    }
                }
            }

            if ($procedure->jugement) {
                $procedure->est_condamne = $procedure->jugement->verdict === 'Condamnation';
                if ($procedure->est_condamne) {
                    $procedure->peine_principale = $procedure->jugement->peine_principale;
                    $procedure->peine_description = $procedure->jugement->peines_complementaires;
                    $procedure->date_condamnation = $procedure->jugement->date_jugement;
                }
            }
        }

        $pdf = Pdf::loadView('pdf.casier_judiciaire', [
            'militaire' => $militaire,
            'procedures' => $procedures,
            'date_edition' => now()->format('d/m/Y à H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream("casier-{$militaire->matricule}.pdf");
    }

    /**
     * Génération du casier judiciaire PDF (méthode legacy)
     */
    public function imprimerCasier(Militaire $militaire)
    {
        return $this->exportCasierPdf($militaire);
    }

    /**
     * Recherche AJAX de personnels pour autocomplétion
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $termes = explode(' ', trim($query));

        $militaires = Militaire::select('id', 'matricule', 'nom', 'prenoms', 'grade_id', 'type_personnel', 'profession')
            ->with(['grade:id,libelle,abreviation', 'armeeRelation'])
            ->where(function ($q) use ($termes) {
                foreach ($termes as $terme) {
                    $terme = trim($terme);
                    if (strlen($terme) >= 2) {
                        $q->where(function ($subQ) use ($terme) {
                            $subQ->where('nom', 'ILIKE', "%{$terme}%")
                                 ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                                 ->orWhere('matricule', 'ILIKE', "%{$terme}%")
                                 ->orWhere('profession', 'ILIKE', "%{$terme}%");
                        });
                    }
                }
            })
            ->orderBy('nom')
            ->limit(20)
            ->get()
            ->map(function ($m) {
                $sublabel = $m->type_personnel === 'militaire' 
                    ? "{$m->matricule} - " . ($m->grade->libelle ?? 'N/A')
                    : "Civil - {$m->profession}";
                
                if ($m->armeeRelation?->nom) {
                    $sublabel .= " - {$m->armeeRelation->nom}";
                }
                
                return [
                    'value' => $m->id,
                    'label' => "{$m->nom} {$m->prenoms}",
                    'sublabel' => $sublabel,
                    'type' => $m->type_personnel,
                ];
            });

        return response()->json($militaires);
    }

    /**
     * API: Récupérer toutes les armées
     */
    public function getArmees()
    {
        return response()->json(Armee::orderBy('nom')->get());
    }

    /**
     * API: Créer une nouvelle armée
     */
    public function storeArmee(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:armees,nom',
            'code' => 'nullable|string|max:20',
        ]);

        $armee = Armee::create($validated);

        return response()->json($armee, 201);
    }
}