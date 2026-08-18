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
use App\Models\Parquet;
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
                'militaire:id,matricule,nom,prenoms,grade_id,unite,type_personnel,profession,armee_id',
                'militaire.grade',
                'militaire.armeeRelation',
                'procedureMilitaires.militaire.grade',
                'procedureMilitaires.militaire.armeeRelation',
                'parquet'
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
                                              ->orWhere('matricule', 'ILIKE', "%{$terme}%")
                                              ->orWhere('profession', 'ILIKE', "%{$terme}%");
                                     })
                                     ->orWhereHas('procedureMilitaires.militaire', function ($milQ) use ($terme) {
                                         $milQ->where('nom', 'ILIKE', "%{$terme}%")
                                              ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                                              ->orWhere('matricule', 'ILIKE', "%{$terme}%")
                                              ->orWhere('profession', 'ILIKE', "%{$terme}%");
                                     })
                                     ->orWhereHas('parquet', function ($pq) use ($terme) {
                                         $pq->where('nom', 'ILIKE', "%{$terme}%");
                                     });
                            });
                        }
                    }
                });
            })
            ->when($request->mois, function($q) use ($request) {
                return $q->whereMonth('date_ouverture', $request->mois);
            })
            ->when($request->annee, function($q) use ($request) {
                return $q->whereYear('date_ouverture', $request->annee);
            })
            ->when($request->jour, function($q) use ($request) {
                return $q->whereDate('date_ouverture', $request->jour);
            })
            ->when($request->type_personnel, function($q) use ($request) {
                return $q->whereHas('procedureMilitaires', function($subQ) use ($request) {
                    $subQ->where('type_personnel', $request->type_personnel);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $moisOptions = [];
        for ($i = 1; $i <= 12; $i++) {
            $moisOptions[] = ['value' => $i, 'label' => date('F', mktime(0, 0, 0, $i, 1))];
        }

        $anneeOptions = [];
        $anneeActuelle = now()->year;
        for ($i = $anneeActuelle - 5; $i <= $anneeActuelle + 1; $i++) {
            $anneeOptions[] = ['value' => $i, 'label' => $i];
        }

        return Inertia::render('Procedures/Index', [
            'procedures' => $procedures,
            'filters' => $request->only(['phase', 'search', 'mois', 'annee', 'jour', 'type_personnel']),
            'moisOptions' => $moisOptions,
            'anneeOptions' => $anneeOptions,
            'typePersonnelOptions' => [
                ['value' => 'militaire', 'label' => 'Militaire'],
                ['value' => 'civil', 'label' => 'Civil'],
            ],
        ]);
    }

    // ==================== CRÉATION ====================

    public function create()
    {
        $parquets = Parquet::actif()->orderBy('nom')->get();
        $grades = Grade::orderBy('libelle')->get();

        return Inertia::render('Procedures/Create', [
            'militaires' => Militaire::select('id', 'matricule', 'nom', 'prenoms', 'grade', 'type_personnel', 'profession')
                ->orderBy('nom')->limit(100)->get()->map(fn($m) => [
                    'value' => $m->id,
                    'label' => "{$m->nom} {$m->prenoms}",
                    'sublabel' => $m->type_personnel === 'militaire' 
                        ? "{$m->matricule} - " . ($m->grade ?? 'N/A')
                        : "Civil - {$m->profession}",
                    'type' => $m->type_personnel,
                ]),
            'infractions' => InfractionBase::select('id', 'code_infraction', 'libelle', 'classification', 'nature')->orderBy('libelle')->get(),
            'phaseTypes' => PhaseType::orderBy('ordre')->get(),
            'parquets' => $parquets,
            'grades' => $grades,
            'lieuCommissionOptions' => ['Organique', 'Operation'],
            'typePersonnelOptions' => [
                ['value' => 'militaire', 'label' => 'Militaire'],
                ['value' => 'civil', 'label' => 'Civil'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('=== STORE PROCEDURE ===');
        \Log::info('Données reçues:', $request->all());

        DB::beginTransaction();

        try {
            $parquetType = $request->input('parquet_type');
            $parquetId = $request->input('parquet_id');
            $parquetNom = $request->input('parquet_nom');
            $parquetLocalisation = $request->input('parquet_localisation');
            $parquetCode = $request->input('parquet_code');

            if (!$parquetType && $request->has('parquet')) {
                $parquetData = $request->input('parquet');
                $parquetType = $parquetData['type'] ?? 'militaire';
                $parquetId = $parquetData['id'] ?? null;
                $parquetNom = $parquetData['nom'] ?? '';
                $parquetLocalisation = $parquetData['localisation'] ?? '';
                $parquetCode = $parquetData['code'] ?? '';
            }

            if (!$parquetType) {
                $parquetType = 'militaire';
            }

            $rules = [
                'est_plurielle' => 'required|boolean',
                'militaires' => 'required|array|min:1',
                'militaires.*.type_personnel' => 'nullable|in:militaire,civil',
                'militaires.*.militaire_id' => 'nullable|exists:militaires,id',
                'militaires.*.nom' => 'nullable|string|max:255',
                'militaires.*.prenom' => 'nullable|string|max:255',
                'militaires.*.profession' => 'nullable|string|max:255',
                'date_phase' => 'required|date',
                'phase_type_id' => 'nullable',
                'phase_personnalisee' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'lieu_commission' => 'nullable|in:Organique,Operation',
                'est_condamne' => 'nullable|boolean',
                'peine_principale' => 'nullable|string|max:255',
                'peine_description' => 'nullable|string',
            ];

            if ($parquetType === 'militaire' && $parquetId) {
                $rules['parquet_id'] = 'exists:parquets,id';
            } elseif ($parquetType === 'droit_commun' && $parquetNom) {
                $rules['parquet_nom'] = 'string|max:255';
            }

            if (!$request->has('parquet_type')) {
                $request->merge(['parquet_type' => $parquetType]);
            }

            $request->merge([
                'parquet_type' => $parquetType,
                'parquet_id' => $parquetId,
                'parquet_nom' => $parquetNom,
                'parquet_localisation' => $parquetLocalisation,
                'parquet_code' => $parquetCode,
            ]);

            $validated = $request->validate($rules);

            $parquetIdFinal = null;
            if ($parquetType === 'militaire' && $parquetId) {
                $parquetIdFinal = $parquetId;
            } elseif ($parquetType === 'droit_commun' && $parquetNom) {
                $parquet = Parquet::firstOrCreate(
                    ['nom' => $parquetNom, 'type' => 'droit_commun'],
                    [
                        'localisation' => $parquetLocalisation,
                        'code' => $parquetCode,
                        'is_active' => true,
                    ]
                );
                $parquetIdFinal = $parquet->id;
            }

            // Gestion du type de phase
            $phaseTypeId = null;
            if ($request->phase_type_id && $request->phase_type_id !== 'autre') {
                $phaseTypeId = $request->phase_type_id;
            } elseif ($request->phase_type_id === 'autre' && !empty($request->phase_personnalisee)) {
                $existing = PhaseType::where('libelle', $request->phase_personnalisee)->first();
                if ($existing) {
                    $phaseTypeId = $existing->id;
                } else {
                    $phaseTypeId = null;
                }
            }

            $phaseLibelle = $request->phase_personnalisee
                ?? PhaseType::find($phaseTypeId)?->libelle
                ?? 'Brouillon';

            // ================================================================
            // RÉCUPÉRER LA PEINE DEPUIS LES CHAMPS DYNAMIQUES
            // ================================================================
            $champs = $request->input('champs', []);
            $peinePrincipale = $request->input('peine_principale');
            $peineDescription = $request->input('peine_description');
            $estCondamne = $request->boolean('est_condamne', false);

            // Chercher dans les champs
            foreach ($champs as $champ) {
                if ($champ['cle'] === 'peine' && !empty($champ['valeur'])) {
                    $peinePrincipale = $champ['valeur'];
                    $estCondamne = true;
                }
                if ($champ['cle'] === 'peine_description' && !empty($champ['valeur'])) {
                    $peineDescription = $champ['valeur'];
                }
            }

            if (!empty($peinePrincipale)) {
                $estCondamne = true;
            }

            \Log::info('Store - Condamnation extraite:', [
                'est_condamne' => $estCondamne,
                'peine_principale' => $peinePrincipale,
                'peine_description' => $peineDescription,
            ]);

            // Créer la procédure
            $procedure = Procedure::create([
                'numero_procedure' => Procedure::genererNumero(),
                'est_plurielle' => $request->boolean('est_plurielle'),
                'lieu_commission' => $request->lieu_commission,
                'phase' => $phaseLibelle,
                'date_ouverture' => $request->date_phase,
                'parquet_type' => $parquetType,
                'parquet_id' => $parquetIdFinal,
                'cree_par' => auth()->id(),
                'est_condamne' => $estCondamne,
                'peine_principale' => $peinePrincipale,
                'condamnation_details' => $estCondamne ? [
                    'peine_principale' => $peinePrincipale,
                    'peine_description' => $peineDescription,
                    'date_condamnation' => $request->date_phase,
                ] : null,
            ]);

            // Ajouter les personnels
            $firstMilitaireId = null;
            foreach ($request->militaires as $militaireData) {
                $militaireId = $militaireData['militaire_id'] ?? null;
                $typePersonnel = $militaireData['type_personnel'] ?? 'militaire';

                if (!$militaireId && !empty($militaireData['nom']) && !empty($militaireData['prenom'])) {
                    $newMilitaire = Militaire::create([
                        'type_personnel' => $typePersonnel,
                        'nom' => $militaireData['nom'],
                        'prenoms' => $militaireData['prenom'],
                        'profession' => $militaireData['profession'] ?? null,
                        'grade' => $militaireData['grade'] ?? null,
                        'grade_id' => !empty($militaireData['grade_id']) ? $militaireData['grade_id'] : null,
                        'matricule' => !empty($militaireData['matricule']) ? $militaireData['matricule'] : null,
                        'statut' => 'En activité',
                    ]);
                    $militaireId = $newMilitaire->id;
                }

                if ($militaireId) {
                    if (!$firstMilitaireId) {
                        $firstMilitaireId = $militaireId;
                    }

                    ProcedureMilitaire::create([
                        'procedure_id' => $procedure->id,
                        'type_personnel' => $typePersonnel,
                        'militaire_id' => $militaireId,
                        'infractions' => $militaireData['infractions'] ?? [],
                        'fautes_militaires' => $militaireData['fautes_militaires'] ?? [],
                        'parties_civiles' => $militaireData['parties_civiles'] ?? [],
                        'temoins' => $militaireData['temoins'] ?? [],
                        'civile_responsables' => $militaireData['civile_responsables'] ?? [],
                        'garants' => $militaireData['garants'] ?? [],
                        'avocats' => $militaireData['avocats'] ?? [],
                        'est_nouveau' => !($militaireData['militaire_id'] ?? false),
                        'nom_temp' => $militaireData['nom'] ?? null,
                        'prenom_temp' => $militaireData['prenom'] ?? null,
                        'grade_temp' => $militaireData['grade'] ?? null,
                        'matricule_temp' => $militaireData['matricule'] ?? null,
                        'profession_temp' => $militaireData['profession'] ?? null,
                    ]);
                }
            }

            if ($firstMilitaireId && count($request->militaires) === 1) {
                $procedure->update(['militaire_id' => $firstMilitaireId]);
            }

            // ================================================================
            // CRÉER LA PHASE INITIALE AVEC CONDAMNATION
            // ================================================================
            $phaseData = [
                'phase_type_id' => $phaseTypeId,
                'phase_personnalisee' => $request->phase_personnalisee,
                'date_phase' => $request->date_phase,
                'description' => $request->description,
                'est_condamne' => $estCondamne,
                'peine_principale' => $peinePrincipale,
                'peine_description' => $peineDescription,
                'champs' => $champs,
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
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
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
            'parquet',
            'procedureMilitaires' => function ($query) {
                $query->with(['militaire.grade', 'militaire.armeeRelation']);
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
            'jugement',
            'createur',
            'validateur',
            'partiesCiviles',
        ]);

        // FORCER la correction des données de condamnation
        if ($procedure->procedurePhases) {
            foreach ($procedure->procedurePhases as $phase) {
                // Vérifier si la phase a une peine dans ses champs
                $hasPeine = false;
                if ($phase->champs) {
                    foreach ($phase->champs as $champ) {
                        if ($champ->cle === 'peine' && !empty($champ->valeur)) {
                            $hasPeine = true;
                            break;
                        }
                    }
                }
                
                if ($hasPeine || !empty($phase->peine_principale)) {
                    $phase->est_condamne = true;
                } else {
                    $phase->est_condamne = (bool) $phase->est_condamne;
                }
            }
        }

        if (!empty($procedure->peine_principale) && !$procedure->est_condamne) {
            $procedure->est_condamne = true;
        }

        $allInfractions = InfractionBase::select('id', 'code_infraction', 'libelle', 'classification', 'nature')
            ->orderBy('libelle')
            ->get();

        $grades = Grade::select('id', 'libelle')->orderBy('libelle')->get();

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

        $allParquets = Parquet::actif()->orderBy('nom')->get();

        return Inertia::render('Procedures/Show', [
            'procedure' => $procedure,
            'phaseTypes' => PhaseType::orderBy('ordre')->get(),
            'parquets' => ['BAMAKO', 'MOPTI', 'GAO', 'KAYES'],
            'infractions' => $allInfractions,
            'grades' => $grades,
            'armees' => $armees,
            'allParquets' => $allParquets,
            'lieuCommissionOptions' => ['Organique', 'Operation'],
        ]);
    }

    // ==================== PHASES ====================

    public function ajouterPhase(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $phasesEffectuees = $procedure->procedurePhases()
            ->whereNotNull('phase_type_id')
            ->with('phaseType')
            ->get()
            ->pluck('phaseType.slug')
            ->filter()
            ->toArray();

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
                $phaseTypeId = null;
            }
        }

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
            'est_condamne' => 'nullable|boolean',
            'peine_principale' => 'nullable|string|max:255',
            'peine_description' => 'nullable|string',
        ]);

        // ================================================================
        // EXTRAIRE LA PEINE DES CHAMPS
        // ================================================================
        $champs = $request->input('champs', []);
        $peinePrincipale = $request->input('peine_principale');
        $peineDescription = $request->input('peine_description');
        $estCondamne = $request->boolean('est_condamne', false);

        // Chercher dans les champs
        foreach ($champs as $champ) {
            if ($champ['cle'] === 'peine' && !empty($champ['valeur'])) {
                $peinePrincipale = $champ['valeur'];
                $estCondamne = true;
            }
            if ($champ['cle'] === 'peine_description' && !empty($champ['valeur'])) {
                $peineDescription = $champ['valeur'];
            }
        }

        if (!empty($peinePrincipale)) {
            $estCondamne = true;
        }

        \Log::info('ajouterPhase - Condamnation extraite:', [
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
            'peine_description' => $peineDescription,
        ]);

        $validated['est_condamne'] = $estCondamne;
        $validated['peine_principale'] = $peinePrincipale;
        $validated['peine_description'] = $peineDescription;

        $ordre = ($procedure->procedurePhases()->max('ordre') ?? 0) + 1;

        $phase = $this->creerPhase($procedure, array_merge($validated, ['phase_type_id' => $phaseTypeId]), $ordre, $request);

        $procedure->update([
            'phase' => $phase->libelle,
            'valide_par' => auth()->id(),
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
        ]);

        return redirect()->back()->with('success', "Phase « {$phase->libelle} » ajoutée avec succès.");
    }

    public function updatePhase(Request $request, Procedure $procedure, $phaseId)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $phase = $procedure->procedurePhases()->findOrFail($phaseId);

        // ================================================================
        // RÉCUPÉRER LA PEINE DEPUIS LES CHAMPS
        // ================================================================
        $champs = $request->input('champs', []);
        $peinePrincipale = $request->input('peine_principale');
        $peineDescription = $request->input('peine_description');
        $estCondamne = $request->boolean('est_condamne', false);

        // Chercher dans les champs
        foreach ($champs as $champ) {
            if ($champ['cle'] === 'peine' && !empty($champ['valeur'])) {
                $peinePrincipale = $champ['valeur'];
                $estCondamne = true;
            }
            if ($champ['cle'] === 'peine_description' && !empty($champ['valeur'])) {
                $peineDescription = $champ['valeur'];
            }
        }

        if (!empty($peinePrincipale)) {
            $estCondamne = true;
        }

        \Log::info('updatePhase - Condamnation extraite:', [
            'phase_id' => $phaseId,
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
            'peine_description' => $peineDescription,
        ]);

        // ================================================================
        // MISE À JOUR DE LA PHASE
        // ================================================================
        $phase->update([
            'description' => $request->input('description'),
            'date_phase' => $request->input('date_phase'),
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
            'peine_description' => $peineDescription,
        ]);

        // ================================================================
        // MISE À JOUR DE LA PROCÉDURE
        // ================================================================
        if ($estCondamne) {
            $procedure->update([
                'est_condamne' => true,
                'peine_principale' => $peinePrincipale,
                'condamnation_details' => [
                    'phase_id' => $phase->id,
                    'peine_principale' => $peinePrincipale,
                    'peine_description' => $peineDescription,
                    'date_condamnation' => $phase->date_phase,
                ]
            ]);
        } else {
            $autresAvecPeine = $procedure->procedurePhases()
                ->where('id', '!=', $phase->id)
                ->where(function ($q) {
                    $q->where('est_condamne', true)
                      ->orWhere(function ($q2) {
                          $q2->whereNotNull('peine_principale')
                             ->where('peine_principale', '!=', '');
                      });
                })
                ->exists();

            if (!$autresAvecPeine) {
                $procedure->update([
                    'est_condamne' => false,
                    'peine_principale' => null,
                    'condamnation_details' => null,
                ]);
            }
        }

        // ================================================================
        // MISE À JOUR DES CHAMPS DYNAMIQUES
        // ================================================================
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
        if ($request->exists('pieces_jointes')) {
            $pjInput = $request->input('pieces_jointes') ?: [];
            $existingIds = collect($pjInput)->pluck('id')->filter()->toArray();
            
            if (empty($existingIds)) {
                $phase->piecesJointes()->delete();
            } else {
                $phase->piecesJointes()->whereNotIn('id', $existingIds)->delete();
            }
            
            foreach ($pjInput as $i => $pj) {
                if (!empty($pj['nom'])) {
                    $data = ['nom' => $pj['nom'], 'description' => $pj['description'] ?? null, 'ordre' => $i];
                    if ($request->hasFile("pieces_jointes.{$i}.fichier")) {
                        $data['chemin_fichier'] = $request->file("pieces_jointes.{$i}.fichier")->store('pieces_jointes', 'public');
                    } elseif (isset($pj['_supprimerFichier']) && $pj['_supprimerFichier']) {
                        $data['chemin_fichier'] = null;
                    }
                    
                    if (!empty($pj['id'])) {
                        $pjm = $phase->piecesJointes()->find($pj['id']);
                        if ($pjm) {
                            if (isset($data['chemin_fichier']) && $data['chemin_fichier'] === null && $pjm->chemin_fichier) {
                                \Storage::disk('public')->delete($pjm->chemin_fichier);
                            }
                            $pjm->update($data);
                        }
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
        // ================================================================
        // EXTRAIRE LA PEINE DES CHAMPS
        // ================================================================
        $peinePrincipale = isset($data['peine_principale']) ? $data['peine_principale'] : null;
        $peineDescription = isset($data['peine_description']) ? $data['peine_description'] : null;
        $estCondamne = isset($data['est_condamne']) ? (bool) $data['est_condamne'] : false;

        // Chercher dans les champs
        if (!empty($data['champs'])) {
            foreach ($data['champs'] as $champ) {
                if ($champ['cle'] === 'peine' && !empty($champ['valeur'])) {
                    $peinePrincipale = $champ['valeur'];
                    $estCondamne = true;
                }
                if ($champ['cle'] === 'peine_description' && !empty($champ['valeur'])) {
                    $peineDescription = $champ['valeur'];
                }
            }
        }

        if (!empty($peinePrincipale)) {
            $estCondamne = true;
        }

        \Log::info('creerPhase - Sauvegarde condamnation:', [
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
            'peine_description' => $peineDescription,
        ]);

        // ================================================================
        // CRÉATION DE LA PHASE
        // ================================================================
        $phase = $procedure->procedurePhases()->create([
            'phase_type_id' => $data['phase_type_id'] ?? null,
            'libelle_personnalisee' => $data['phase_personnalisee'] ?? null,
            'date_phase' => $data['date_phase'],
            'description' => $data['description'] ?? null,
            'est_condamne' => $estCondamne,
            'peine_principale' => $peinePrincipale,
            'peine_description' => $peineDescription,
            'ordre' => $ordre,
            'est_retour' => $estRetour,
            'phase_precedente_id' => $data['phase_precedente_id'] ?? null,
            'cree_par' => auth()->id(),
        ]);

        // ================================================================
        // MISE À JOUR DE LA PROCÉDURE SI CONDAMNÉ
        // ================================================================
        if ($estCondamne) {
            $procedure->update([
                'est_condamne' => true,
                'peine_principale' => $peinePrincipale,
                'condamnation_details' => [
                    'phase_id' => $phase->id,
                    'peine_principale' => $peinePrincipale,
                    'peine_description' => $peineDescription,
                    'date_condamnation' => $data['date_phase'],
                ]
            ]);
        }

        // ================================================================
        // CHAMPS DYNAMIQUES
        // ================================================================
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
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'parquet_type' => 'required|in:militaire,droit_commun',
            'parquet_id' => 'nullable|exists:parquets,id',
            'parquet_nom' => 'nullable|string|max:255',
            'parquet_localisation' => 'nullable|string|max:255',
            'parquet_code' => 'nullable|string|max:50',
        ]);

        $parquetId = null;
        if ($request->parquet_type === 'militaire') {
            if ($request->parquet_id) {
                $parquetId = $request->parquet_id;
            }
        } else {
            if ($request->parquet_nom) {
                $parquet = Parquet::firstOrCreate(
                    ['nom' => $request->parquet_nom, 'type' => 'droit_commun'],
                    [
                        'localisation' => $request->parquet_localisation,
                        'code' => $request->parquet_code,
                        'is_active' => true,
                    ]
                );
                $parquetId = $parquet->id;
            }
        }

        $procedure->update([
            'parquet_type' => $request->parquet_type,
            'parquet_id' => $parquetId,
        ]);

        return redirect()->back()->with('success', 'Parquet mis à jour.');
    }

    public function updateDateOuverture(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }
        $request->validate(['date_ouverture' => 'required|date']);
        $procedure->update(['date_ouverture' => $request->date_ouverture]);
        return redirect()->back()->with('success', 'Date mise à jour.');
    }

    // ==================== MILITAIRES DANS PROCEDURE ====================

    public function ajouterMilitaire(Request $request, Procedure $procedure)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $request->validate([
            'type_personnel' => 'nullable|in:militaire,civil',
            'militaire_id' => 'nullable|exists:militaires,id',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'grade_id' => 'nullable|exists:grades,id',
            'grade' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
        ]);

        $militaireId = $request->militaire_id;
        $typePersonnel = $request->type_personnel ?? 'militaire';

        if (!$militaireId && !empty($request->nom) && !empty($request->prenom)) {
            $newMilitaire = Militaire::create([
                'type_personnel' => $typePersonnel,
                'nom' => $request->nom,
                'prenoms' => $request->prenom,
                'profession' => $request->profession ?? null,
                'grade_id' => !empty($request->grade_id) ? $request->grade_id : null,
                'matricule' => !empty($request->matricule) ? $request->matricule : null,
                'statut' => 'En activité',
            ]);
            $militaireId = $newMilitaire->id;
        }

        if (!$militaireId) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un personnel existant ou créer un nouveau avec nom et prénom.');
        }

        $exists = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('militaire_id', $militaireId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Ce personnel est déjà associé à cette procédure.');
        }

        ProcedureMilitaire::create([
            'procedure_id' => $procedure->id,
            'type_personnel' => $typePersonnel,
            'militaire_id' => $militaireId,
            'infractions' => [],
            'fautes_militaires' => [],
            'parties_civiles' => [],
            'est_nouveau' => false,
        ]);

        $count = ProcedureMilitaire::where('procedure_id', $procedure->id)->count();
        if ($count > 1) {
            $procedure->update(['est_plurielle' => true]);
        }

        if ($count === 1) {
            $procedure->update(['militaire_id' => $militaireId]);
        }

        return redirect()->back()->with('success', 'Personnel ajouté avec succès.');
    }

    public function updateMilitaireInfractions(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutModifierProcedure()) {
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

        return redirect()->back()->with('success', 'Infractions mises à jour pour le personnel.');
    }

    public function updateMilitaireFautes(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'fautes_militaires' => 'nullable|array',
            'fautes_militaires.*' => 'nullable|integer|exists:fautes_militaires,id',
        ]);

        $procedureMilitaire->update([
            'fautes_militaires' => $request->fautes_militaires ?? [],
        ]);

        return redirect()->back()->with('success', 'Fautes mises à jour pour le personnel.');
    }

    public function updateMilitairePartiesCiviles(Request $request, Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutModifierProcedure()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $request->validate([
            'parties_civiles' => 'nullable|array',
            'parties_civiles.*.type' => 'required|in:Personne,Structure',
            'parties_civiles.*.nom' => 'required|string|max:255',
            'temoins' => 'nullable|array',
            'civile_responsables' => 'nullable|array',
            'garants' => 'nullable|array',
            'avocats' => 'nullable|array',
        ]);

        $procedureMilitaire->update([
            'parties_civiles' => $request->parties_civiles ?? [],
            'temoins' => $request->temoins ?? [],
            'civile_responsables' => $request->civile_responsables ?? [],
            'garants' => $request->garants ?? [],
            'avocats' => $request->avocats ?? [],
        ]);

        return redirect()->back()->with('success', 'Acteurs annexes mis à jour pour le personnel.');
    }

    public function supprimerMilitaire(Procedure $procedure, $procedureMilitaireId)
    {
        if (!auth()->user()->peutValiderPhase()) {
            return redirect()->back()->with('error', 'Action non autorisée.');
        }

        $procedureMilitaire = ProcedureMilitaire::where('procedure_id', $procedure->id)
            ->where('id', $procedureMilitaireId)
            ->firstOrFail();

        $count = ProcedureMilitaire::where('procedure_id', $procedure->id)->count();
        if ($count <= 1) {
            return redirect()->back()->with('error', 'Impossible de supprimer le dernier personnel de la procédure.');
        }

        if ($procedureMilitaire->militaire_id == $procedure->militaire_id) {
            $newPrincipal = ProcedureMilitaire::where('procedure_id', $procedure->id)
                ->where('id', '!=', $procedureMilitaireId)
                ->first();
            if ($newPrincipal) {
                $procedure->update(['militaire_id' => $newPrincipal->militaire_id]);
            }
        }

        $procedureMilitaire->delete();

        $remaining = ProcedureMilitaire::where('procedure_id', $procedure->id)->count();
        if ($remaining <= 1) {
            $procedure->update(['est_plurielle' => false]);
        }

        return redirect()->back()->with('success', 'Personnel retiré de la procédure.');
    }

    // ==================== SUPPRESSION ====================

    public function destroy(Procedure $procedure)
    {
        if (auth()->user()->role !== 'ADMIN') {
            return redirect()->back()->with('error', 'Seul l\'Administrateur peut supprimer.');
        }

        $numero = $procedure->numero_procedure;

        $procedure->infractions()->detach();
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
            'parquet',
            'infractions',
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

    public function getPhaseTypes()
    {
        return response()->json(PhaseType::orderBy('ordre')->get());
    }

    public function getParquets()
    {
        return response()->json(Parquet::actif()->orderBy('nom')->get());
    }

    public function getGrades()
    {
        return response()->json(Grade::orderBy('libelle')->get());
    }

    public function createParquet(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255|unique:parquets,nom',
            'localisation' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        $parquet = Parquet::create([
            'nom' => $request->nom,
            'type' => 'droit_commun',
            'localisation' => $request->localisation,
            'code' => $request->code,
            'is_active' => true,
        ]);

        return response()->json($parquet, 201);
    }

    public function searchMilitaires(Request $request)
    {
        $search = $request->get('q', '');
        $typePersonnel = $request->get('type', null);

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $termes = explode(' ', trim($search));

        $query = Militaire::recherche($search)
            ->select('id', 'matricule', 'nom', 'prenoms', 'grade', 'type_personnel', 'profession');

        if ($typePersonnel) {
            $query->where('type_personnel', $typePersonnel);
        }

        $militaires = $query->limit(20)
            ->get()
            ->map(fn($m) => [
                'value' => $m->id,
                'label' => "{$m->nom} {$m->prenoms}",
                'sublabel' => $m->type_personnel === 'militaire' 
                    ? "{$m->matricule} - {$m->grade}" 
                    : "Civil - {$m->profession}",
                'type' => $m->type_personnel,
            ]);

        return response()->json($militaires);
    }
}