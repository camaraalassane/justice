<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Militaire;
use App\Models\InfractionBase;
use App\Models\PartieCivile;
use App\Models\FauteMilitaire;
use App\Models\PhaseType;
use App\Models\ProcedurePhase;
use App\Models\ProcedureMilitaire;
use App\Models\Grade;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProcedureController extends Controller
{
    use LogsActivity;

    // ==================== LISTE ====================

    public function index(Request $request)
    {
        $procedures = Procedure::with([
                'militaire:id,matricule,nom,prenoms,grade,unite',
                'procedureMilitaires.militaire'
            ])
            ->when($request->phase, fn($q) => $q->parPhase($request->phase))
            ->when($request->search, function ($query) use ($request) {
                $termes = explode(' ', trim($request->search));
                $query->where(function ($q) use ($termes) {
                    foreach ($termes as $terme) {
                        $terme = trim($terme);
                        if (strlen($terme) >= 2) {
                            $q->where(function ($subQ) use ($terme) {
                                $subQ->where('numero_procedure', 'ILIKE', "%{$terme}%")
                                     ->orWhereHas('militaire', function ($milQ) use ($terme) {
                                         $milQ->where('nom', 'ILIKE', "%{$terme}%")
                                              ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                                              ->orWhere('matricule', 'ILIKE', "%{$terme}%");
                                     })
                                     ->orWhereHas('procedureMilitaires.militaire', function ($milQ) use ($terme) {
                                         $milQ->where('nom', 'ILIKE', "%{$terme}%")
                                              ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                                              ->orWhere('matricule', 'ILIKE', "%{$terme}%");
                                     });
                            });
                        }
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Procedures/Index', [
            'procedures' => $procedures,
            'filters' => $request->only(['phase', 'search']),
        ]);
    }

    // ==================== CRÉATION ====================

    public function create()
    {
        return Inertia::render('Procedures/Create', [
            'militaires' => Militaire::select('id', 'matricule', 'nom', 'prenoms', 'grade')
                ->orderBy('nom')->limit(100)->get()->map(fn($m) => [
                    'value' => $m->id,
                    'label' => "{$m->nom} {$m->prenoms}",
                    'sublabel' => "{$m->matricule} - {$m->grade}"
                ]),
            'infractions' => InfractionBase::select('id', 'code_infraction', 'libelle', 'classification', 'nature')->orderBy('libelle')->get(),
            'phaseTypes' => PhaseType::orderBy('ordre')->get(),
            'parquets' => ['BAMAKO', 'MOPTI', 'GAO', 'KAYES'],
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('=== STORE PROCEDURE ===');
        \Log::info('Données reçues:', $request->all());

        DB::beginTransaction();

        try {
            // Validation
            $request->validate([
                'est_plurielle' => 'required|boolean',
                'militaires' => 'required|array|min:1',
                'militaires.*.militaire_id' => 'nullable|exists:militaires,id',
                'militaires.*.nom' => 'nullable|string|max:255',
                'militaires.*.prenom' => 'nullable|string|max:255',
                'parquet_competent' => 'required|in:BAMAKO,MOPTI,GAO,KAYES',
                'date_phase' => 'required|date',
                'phase_type_id' => 'nullable',
                'phase_personnalisee' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            // Gestion du type de phase
            $phaseTypeId = null;
            if ($request->phase_type_id && $request->phase_type_id !== 'autre') {
                $phaseTypeId = $request->phase_type_id;
            } elseif ($request->phase_type_id === 'autre' && !empty($request->phase_personnalisee)) {
                $existing = PhaseType::where('libelle', $request->phase_personnalisee)->first();
                if ($existing) {
                    $phaseTypeId = $existing->id;
                } else {
                    $newPhaseType = PhaseType::create([
                        'libelle' => $request->phase_personnalisee,
                        'slug' => str()->slug($request->phase_personnalisee),
                        'is_system' => false,
                        'is_custom' => true,
                        'ordre' => PhaseType::max('ordre') + 1,
                    ]);
                    $phaseTypeId = $newPhaseType->id;
                }
            }

            $phaseLibelle = $request->phase_personnalisee
                ?? PhaseType::find($phaseTypeId)?->libelle
                ?? 'Brouillon';

            // Créer la procédure
            $procedure = Procedure::create([
                'numero_procedure' => Procedure::genererNumero(),
                'est_plurielle' => $request->boolean('est_plurielle'),
                'phase' => $phaseLibelle,
                'date_ouverture' => $request->date_phase,
                'parquet_competent' => $request->parquet_competent,
                'cree_par' => auth()->id(),
            ]);

            \Log::info('Procédure créée:', ['id' => $procedure->id]);

            // Ajouter les militaires
            $firstMilitaireId = null;
            foreach ($request->militaires as $militaireData) {
                $militaireId = $militaireData['militaire_id'] ?? null;

                if (!$militaireId && !empty($militaireData['nom']) && !empty($militaireData['prenom'])) {
                    $newMilitaire = Militaire::create([
                        'nom' => $militaireData['nom'],
                        'prenoms' => $militaireData['prenom'],
                        'grade' => $militaireData['grade'] ?? null,
                        'matricule' => $militaireData['matricule'] ?? null,
                        'statut' => 'Actif',
                    ]);
                    $militaireId = $newMilitaire->id;
                    \Log::info('Nouveau militaire créé:', ['id' => $militaireId]);
                }

                if ($militaireId) {
                    if (!$firstMilitaireId) {
                        $firstMilitaireId = $militaireId;
                    }

                    ProcedureMilitaire::create([
                        'procedure_id' => $procedure->id,
                        'militaire_id' => $militaireId,
                        'infractions' => $militaireData['infractions'] ?? [],
                        'fautes_militaires' => $militaireData['fautes_militaires'] ?? [],
                        'parties_civiles' => $militaireData['parties_civiles'] ?? [],
                        'est_nouveau' => !($militaireData['militaire_id'] ?? false),
                        'nom_temp' => $militaireData['nom'] ?? null,
                        'prenom_temp' => $militaireData['prenom'] ?? null,
                        'grade_temp' => $militaireData['grade'] ?? null,
                        'matricule_temp' => $militaireData['matricule'] ?? null,
                    ]);
                    \Log::info('Liaison créée');
                }
            }

            if ($firstMilitaireId && count($request->militaires) === 1) {
                $procedure->update(['militaire_id' => $firstMilitaireId]);
            }

            // Créer la phase initiale
            $phaseData = [
                'phase_type_id' => $phaseTypeId,
                'phase_personnalisee' => $request->phase_personnalisee,
                'date_phase' => $request->date_phase,
                'description' => $request->description,
                'champs' => $request->champs ?? [],
                'personnes' => $request->personnes ?? [],
                'evenements' => $request->evenements ?? [],
                'references' => $request->references ?? [],
                'options_cocher' => $request->options_cocher ?? [],
                'pieces_jointes' => $request->pieces_jointes ?? [],
            ];
            
            $this->creerPhase($procedure, $phaseData, 1, $request);
            $procedure->update(['phase' => $phaseLibelle]);

            DB::commit();

            return redirect()->route('procedures.show', $procedure->id)
                ->with('success', 'Procédure créée avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Erreurs de validation:', $e->errors());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur générale:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    // ==================== AFFICHAGE ====================

    public function show(Procedure $procedure)
    {
        $procedure->load([
            'militaire',
            'procedureMilitaires' => function ($query) {
                $query->with('militaire');
            },
            'procedurePhases' => function ($q) {
                $q->orderBy('ordre', 'desc')->with([
                    'phaseType',
                    'createur',
                    'phasePrecedente',
                    'champs',
                    'personnes',
                    'evenements',
                    'references',
                    'piecesJointes',
                    'optionsCocher'
                ]);
            },
            'infractions',
            'fautesMilitaires',
            'jugement',
            'createur',
            'validateur',
            'partiesCiviles',
        ]);

        // Récupérer toutes les infractions
        $allInfractions = InfractionBase::select('id', 'code_infraction', 'libelle', 'classification', 'nature')
            ->orderBy('libelle')
            ->get();

        // Récupérer les grades pour les selects
        $grades = Grade::select('id', 'libelle')->orderBy('libelle')->get();

        // Récupérer les armees
        $armees = [
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
            'Autre'
        ];

        return Inertia::render('Procedures/Show', [
            'procedure' => $procedure,
            'phaseTypes' => PhaseType::orderBy('ordre')->get(),
            'parquets' => ['BAMAKO', 'MOPTI', 'GAO', 'KAYES'],
            'infractions' => $allInfractions,
            'grades' => $grades,
            'armees' => $armees,
        ]);
    }

    // ==================== PHASES ====================

    public function ajouterPhase(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $phasesEffectuees = $procedure->procedurePhases()
            ->whereNotNull('phase_type_id')
            ->with('phaseType')
            ->get()
            ->pluck('phaseType.slug')
            ->filter()
            ->toArray();

        // ========== GESTION DU TYPE DE PHASE ==========
        $phaseTypeId = null;
        if ($request->phase_type_id && $request->phase_type_id !== 'autre') {
            $phaseType = PhaseType::find($request->phase_type_id);
            if ($phaseType && in_array($phaseType->slug, $phasesEffectuees)) {
                return redirect()->back()->with('error', "La phase « {$phaseType->libelle} » a déjà été effectuée.");
            }
            $phaseTypeId = $request->phase_type_id;
            $request->validate(['phase_type_id' => 'exists:phase_types,id']);
        } elseif ($request->phase_type_id === 'autre' && !empty($request->phase_personnalisee)) {
            $existing = PhaseType::where('libelle', $request->phase_personnalisee)->first();
            if ($existing) {
                if (in_array($existing->slug, $phasesEffectuees)) {
                    return redirect()->back()->with('error', "La phase « {$existing->libelle} » a déjà été effectuée.");
                }
                $phaseTypeId = $existing->id;
            } else {
                $newPhaseType = PhaseType::create([
                    'libelle' => $request->phase_personnalisee,
                    'slug' => str()->slug($request->phase_personnalisee),
                    'is_system' => false,
                    'is_custom' => true,
                    'ordre' => PhaseType::max('ordre') + 1,
                ]);
                $phaseTypeId = $newPhaseType->id;
            }
        }
        // ========== FIN GESTION ==========

        $validated = $request->validate([
            'phase_type_id' => 'nullable',
            'phase_personnalisee' => 'nullable|string|max:255',
            'date_phase' => 'required|date',
            'description' => 'nullable|string',
            'champs' => 'nullable|array',
            'personnes' => 'nullable|array',
            'evenements' => 'nullable|array',
            'references' => 'nullable|array',
            'options_cocher' => 'nullable|array',
            'pieces_jointes' => 'nullable|array',
        ]);

        $ordre = ($procedure->procedurePhases()->max('ordre') ?? 0) + 1;

        $phase = $this->creerPhase($procedure, array_merge($validated, ['phase_type_id' => $phaseTypeId]), $ordre, $request);

        $procedure->update(['phase' => $phase->libelle, 'valide_par' => auth()->id()]);

        return redirect()->back()->with('success', "Phase « {$phase->libelle} » ajoutée avec succès.");
    }

    public function updatePhase(Request $request, Procedure $procedure, $phaseId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $phase = $procedure->procedurePhases()->findOrFail($phaseId);
        $phase->update($request->only('description', 'date_phase'));

        if ($request->has('champs')) {
            $phase->champs()->delete();
            foreach ($request->champs as $i => $ch) {
                if (!empty($ch['cle'])) {
                    $phase->champs()->create(array_merge($ch, ['ordre' => $i]));
                }
            }
        }
        if ($request->has('personnes')) {
            $phase->personnes()->delete();
            foreach ($request->personnes as $i => $p) {
                if (!empty($p['nom'])) {
                    $phase->personnes()->create(array_merge($p, ['ordre' => $i]));
                }
            }
        }
        if ($request->has('evenements')) {
            $phase->evenements()->delete();
            foreach ($request->evenements as $i => $e) {
                if (!empty($e['nom'])) {
                    $phase->evenements()->create(array_merge($e, ['ordre' => $i]));
                }
            }
        }
        if ($request->has('references')) {
            $phase->references()->delete();
            foreach ($request->references as $i => $r) {
                if (!empty($r['libelle'])) {
                    $phase->references()->create(array_merge($r, ['ordre' => $i]));
                }
            }
        }
        if ($request->has('options_cocher')) {
            $phase->optionsCocher()->delete();
            foreach ($request->options_cocher as $i => $o) {
                $phase->optionsCocher()->create(array_merge($o, ['ordre' => $i]));
            }
        }
        if ($request->has('pieces_jointes')) {
            $existingIds = collect($request->pieces_jointes)->pluck('id')->filter()->toArray();
            $phase->piecesJointes()->whereNotIn('id', $existingIds)->delete();
            foreach ($request->pieces_jointes as $i => $pj) {
                if (!empty($pj['nom'])) {
                    $data = ['nom' => $pj['nom'], 'description' => $pj['description'] ?? null, 'ordre' => $i];
                    if ($request->hasFile("pieces_jointes.{$i}.fichier")) {
                        $data['chemin_fichier'] = $request->file("pieces_jointes.{$i}.fichier")->store('pieces_jointes', 'public');
                    }
                    if (!empty($pj['id'])) {
                        $pjm = $phase->piecesJointes()->find($pj['id']);
                        if ($pjm) $pjm->update($data);
                    } else {
                        $phase->piecesJointes()->create($data);
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Phase mise à jour.');
    }

    private function creerPhase(Procedure $procedure, array $data, int $ordre, Request $request = null, bool $estRetour = false): ProcedurePhase
    {
        $phase = $procedure->procedurePhases()->create([
            'phase_type_id' => $data['phase_type_id'] ?? null,
            'libelle_personnalisee' => $data['phase_personnalisee'] ?? null,
            'date_phase' => $data['date_phase'],
            'description' => $data['description'] ?? null,
            'ordre' => $ordre,
            'est_retour' => $estRetour,
            'phase_precedente_id' => $data['phase_precedente_id'] ?? null,
            'cree_par' => auth()->id(),
        ]);

        if (!empty($data['champs'])) {
            foreach ($data['champs'] as $i => $ch) {
                if (!empty($ch['cle'])) {
                    $phase->champs()->create(array_merge($ch, ['ordre' => $i]));
                }
            }
        }
        if (!empty($data['personnes'])) {
            foreach ($data['personnes'] as $i => $p) {
                if (!empty($p['nom'])) {
                    $phase->personnes()->create(array_merge($p, ['ordre' => $i]));
                }
            }
        }
        if (!empty($data['evenements'])) {
            foreach ($data['evenements'] as $i => $e) {
                if (!empty($e['nom'])) {
                    $phase->evenements()->create(array_merge($e, ['ordre' => $i]));
                }
            }
        }
        if (!empty($data['references'])) {
            foreach ($data['references'] as $i => $r) {
                if (!empty($r['libelle'])) {
                    $phase->references()->create(array_merge($r, ['ordre' => $i]));
                }
            }
        }
        if (!empty($data['options_cocher'])) {
            foreach ($data['options_cocher'] as $i => $o) {
                $phase->optionsCocher()->create(array_merge($o, ['ordre' => $i]));
            }
        }
        if (!empty($data['pieces_jointes'])) {
            foreach ($data['pieces_jointes'] as $i => $pj) {
                $nomPj = $pj['nom'] ?? null;
                $descPj = $pj['description'] ?? null;
                $cheminPj = $pj['chemin_fichier'] ?? null;
                $ctxPj = $pj['contexte'] ?? null;
                $hasFichier = $request && $request->hasFile("pieces_jointes.{$i}.fichier");
                if (empty($nomPj) && !$hasFichier && empty($cheminPj)) continue;
                $chemin = null;
                if ($hasFichier) {
                    $file = $request->file("pieces_jointes.{$i}.fichier");
                    if ($file && $file->isValid()) {
                        $chemin = $file->store('pieces_jointes', 'public');
                    }
                }
                $phase->piecesJointes()->create([
                    'nom' => $nomPj ?: 'Document sans nom',
                    'description' => $descPj,
                    'chemin_fichier' => $chemin ?? $cheminPj,
                    'contexte' => $ctxPj,
                    'ordre' => $i
                ]);
            }
        }

        return $phase;
    }

    // ==================== MODIFICATIONS RAPIDES ====================

    public function updateParquet(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $request->validate(['parquet_competent' => 'required|in:BAMAKO,MOPTI,GAO,KAYES']);
        $procedure->update(['parquet_competent' => $request->parquet_competent]);
        return redirect()->back()->with('success', 'Parquet mis à jour.');
    }

    public function updateDateOuverture(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $request->validate(['date_ouverture' => 'required|date']);
        $procedure->update(['date_ouverture' => $request->date_ouverture]);
        return redirect()->back()->with('success', 'Date mise à jour.');
    }

    public function updateInfractions(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $request->validate(['selectedInfractions' => 'required|array|min:1']);
        $procedure->infractions()->detach();
        foreach ($request->selectedInfractions as $iid) {
            $procedure->infractions()->attach($iid);
        }
        return redirect()->back()->with('success', 'Infractions mises à jour.');
    }

    public function updatePartiesCiviles(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $request->validate([
            'parties_civiles' => 'required|array|min:1',
            'parties_civiles.*.type' => 'required|in:Personne,Structure',
            'parties_civiles.*.nom' => 'required|string|max:255',
            'parties_civiles.*.prenom' => 'required_if:parties_civiles.*.type,Personne|nullable|string|max:255',
            'parties_civiles.*.profession' => 'nullable|string|max:255',
            'parties_civiles.*.adresse' => 'nullable|string|max:255',
        ]);
        $procedure->partiesCiviles()->delete();
        foreach ($request->parties_civiles as $pc) {
            PartieCivile::create(array_merge(['procedure_id' => $procedure->id], $pc));
        }
        return redirect()->back()->with('success', 'Parties civiles mises à jour.');
    }

    public function updateFautes(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $procedure->fautesMilitaires()->delete();
        if (!empty($request->fautes_militaires)) {
            foreach ($request->fautes_militaires as $i => $f) {
                if (!empty($f['libelle'])) {
                    FauteMilitaire::create([
                        'procedure_id' => $procedure->id,
                        'libelle' => $f['libelle'],
                        'description' => $f['description'] ?? null,
                        'ordre' => $i
                    ]);
                }
            }
        }
        return redirect()->back()->with('success', 'Fautes mises à jour.');
    }

    // ==================== MISE À JOUR INDIVIDUELLE DES MILITAIRES ====================

    public function updateMilitaireInfractions(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'infractions' => 'required|array',
            'infractions.*' => 'exists:infractions_base,id',
        ]);

        $procedureMilitaire->update([
            'infractions' => $request->infractions,
        ]);

        return redirect()->back()->with('success', 'Infractions mises à jour pour le militaire.');
    }

    public function updateMilitaireFautes(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'fautes_militaires' => 'nullable|array',
            'fautes_militaires.*.libelle' => 'nullable|string|max:255',
            'fautes_militaires.*.description' => 'nullable|string',
        ]);

        $procedureMilitaire->update([
            'fautes_militaires' => $request->fautes_militaires ?? [],
        ]);

        return redirect()->back()->with('success', 'Fautes mises à jour pour le militaire.');
    }

    public function updateMilitairePartiesCiviles(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'parties_civiles' => 'nullable|array',
            'parties_civiles.*.type' => 'required|in:Personne,Structure',
            'parties_civiles.*.nom' => 'required|string|max:255',
            'parties_civiles.*.prenom' => 'required_if:parties_civiles.*.type,Personne|nullable|string|max:255',
            'parties_civiles.*.profession' => 'nullable|string|max:255',
            'parties_civiles.*.adresse' => 'nullable|string|max:255',
        ]);

        $procedureMilitaire->update([
            'parties_civiles' => $request->parties_civiles ?? [],
        ]);

        return redirect()->back()->with('success', 'Parties civiles mises à jour pour le militaire.');
    }

    /**
     * Ajouter un militaire à une procédure existante
     */
    public function ajouterMilitaire(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'militaire_id' => 'nullable|exists:militaires,id',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
        ]);

        $militaireId = $request->militaire_id;

        // Si pas d'ID et que nom et prénom sont fournis, créer le militaire
        if (!$militaireId && !empty($request->nom) && !empty($request->prenom)) {
            $newMilitaire = Militaire::create([
                'nom' => $request->nom,
                'prenoms' => $request->prenom,
                'grade' => $request->grade ?? null,
                'matricule' => $request->matricule ?? null,
                'statut' => 'Actif',
            ]);
            $militaireId = $newMilitaire->id;
        }

        if (!$militaireId) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un militaire existant ou créer un nouveau avec nom et prénom.');
        }

        // Vérifier que le militaire n'est pas déjà associé à la procédure
        $exists = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('militaire_id', $militaireId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Ce militaire est déjà associé à cette procédure.');
        }

        // Créer la liaison
        ProcedureMilitaire::create([
            'procedure_id' => $procedure->id,
            'militaire_id' => $militaireId,
            'infractions' => [],
            'fautes_militaires' => [],
            'parties_civiles' => [],
            'est_nouveau' => false,
        ]);

        // Mettre à jour est_plurielle si plus d'un militaire
        $count = ProcedureMilitaire::where('procedure_id', $procedure->id)->count();
        if ($count > 1) {
            $procedure->update(['est_plurielle' => true]);
        }

        // Si c'est le premier militaire, le définir comme principal
        if ($count === 1) {
            $procedure->update(['militaire_id' => $militaireId]);
        }

        return redirect()->back()->with('success', 'Militaire ajouté avec succès.');
    }

    /**
     * Mettre à jour les informations d'un militaire dans une procédure
     */
    public function updateMilitaireInfo(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'grade' => 'nullable|string|max:255',
            'unite' => 'nullable|string|max:255',
            'genre' => 'nullable|in:Masculin,Féminin',
            'armee' => 'nullable|string|max:255',
            'statut' => 'nullable|in:Actif,Suspendu,Déserteur,Radié',
            'date_naissance' => 'nullable|date',
        ]);

        // Si le militaire existe, mettre à jour ses informations
        if ($procedureMilitaire->militaire_id) {
            $militaire = Militaire::find($procedureMilitaire->militaire_id);
            if ($militaire) {
                $militaire->update([
                    'grade' => $request->grade,
                    'unite' => $request->unite,
                    'genre' => $request->genre,
                    'armee' => $request->armee,
                    'statut' => $request->statut ?? 'Actif',
                    'date_naissance' => $request->date_naissance,
                ]);
            }
        }

        // Mettre à jour les champs temporaires
        $procedureMilitaire->update([
            'grade_temp' => $request->grade,
            'unite_temp' => $request->unite,
        ]);

        return redirect()->back()->with('success', 'Informations du militaire mises à jour.');
    }

    // ==================== SUPPRESSION ====================

    public function destroy(Procedure $procedure)
    {
        if (auth()->user()->role !== 'SD') {
            return redirect()->back()->with('error', 'Seul le SD peut supprimer.');
        }

        $numero = $procedure->numero_procedure;

        // Supprimer les relations
        $procedure->infractions()->detach();
        $procedure->fautesMilitaires()->delete();
        $procedure->partiesCiviles()->delete();
        $procedure->procedureMilitaires()->delete();

        if ($procedure->jugement) {
            $procedure->jugement->delete();
        }

        $procedure->procedurePhases()->delete();

        $this->logDelete($procedure, "Procédure supprimée : {$numero}");
        $procedure->delete();

        return redirect()->route('procedures.index')->with('success', "Procédure {$numero} supprimée.");
    }

    public function retournerPhase(Procedure $procedure, $phaseId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $phase = $procedure->procedurePhases()->findOrFail($phaseId);
        $phasePrecedente = $procedure->procedurePhases()
            ->where('ordre', '<', $phase->ordre)
            ->orderBy('ordre', 'desc')
            ->first();

        $phase->delete();
        $procedure->update(['phase' => $phasePrecedente ? $phasePrecedente->libelle : 'Brouillon']);

        return redirect()->back()->with('success', 'Retour à la phase précédente effectué.');
    }

    // ==================== EXPORT PDF ====================

    public function exportPdf(Procedure $procedure)
    {
        $procedure->load([
            'militaire.grade',
            'infractions',
            'fautesMilitaires',
            'partiesCiviles',
            'jugement',
            'createur',
            'validateur',
            'procedureMilitaires' => function ($query) {
                $query->with(['militaire.grade']);
            },
            'procedurePhases' => function ($q) {
                $q->orderBy('ordre', 'asc')->with([
                    'phaseType',
                    'createur',
                    'champs',
                    'personnes',
                    'evenements',
                    'references',
                    'piecesJointes',
                    'optionsCocher'
                ]);
            },
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.procedure_complete', [
            'procedure' => $procedure,
            'date_edition' => now()->format('d/m/Y à H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("procedure-{$procedure->numero_procedure}.pdf");
    }

    // ==================== API ====================

    /**
     * Récupérer les types de phases (pour le frontend)
     */
    public function getPhaseTypes()
    {
        return response()->json(PhaseType::orderBy('ordre')->get());
    }

    /**
     * Créer un type de phase personnalisé via API
     */
    public function createCustomPhaseType(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:phase_types,libelle',
        ]);

        $phaseType = PhaseType::create([
            'libelle' => $request->libelle,
            'slug' => str()->slug($request->libelle),
            'is_system' => false,
            'is_custom' => true,
            'ordre' => PhaseType::max('ordre') + 1,
        ]);

        return response()->json($phaseType, 201);
    }

    /**
     * Rechercher des militaires
     */
    public function searchMilitaires(Request $request)
    {
        $search = $request->get('q', '');
        $militaires = Militaire::recherche($search)
            ->select('id', 'matricule', 'nom', 'prenoms', 'grade')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'value' => $m->id,
                'label' => "{$m->nom} {$m->prenoms}",
                'sublabel' => "{$m->matricule} - {$m->grade}"
            ]);

        return response()->json($militaires);
    }

    /**
     * Récupérer un militaire d'une procédure
     */
    public function getProcedureMilitaire(Procedure $procedure, $procedureMilitaireId)
    {
        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->with('militaire')
            ->firstOrFail();

        return response()->json($procedureMilitaire);
    }
}