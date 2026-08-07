<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Militaire;
use App\Models\InfractionBase;
use App\Models\ProcedureMilitaire;
use App\Models\Parquet;
use App\Models\FauteMilitaire;
use App\Models\CategorieFaute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // ==================== STATS GÉNÉRALES ====================
        $stats = DB::table('procedures')
            ->selectRaw("
                COUNT(*) as total_procedures,
                COUNT(*) FILTER (WHERE phase ILIKE '%Ordre de Poursuite%') as en_ordre_poursuite,
                COUNT(*) FILTER (WHERE phase ILIKE '%Mise à Disposition%') as en_mise_disposition,
                COUNT(*) FILTER (WHERE phase ILIKE '%Communiqué%') as en_communique,
                COUNT(*) FILTER (WHERE phase ILIKE '%Jugement%') as en_jugement,
                COUNT(*) FILTER (WHERE phase = 'Cloturee') as cloturees
            ")
            ->whereNull('deleted_at')
            ->first();

        $statsCeMois = DB::table('procedures')
            ->selectRaw("COUNT(*) as total_mois, COUNT(*) FILTER (WHERE phase = 'Cloturee') as cloturees_mois")
            ->whereNull('deleted_at')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->first();

        $stats->total_mois = $statsCeMois->total_mois ?? 0;
        $stats->cloturees_mois = $statsCeMois->cloturees_mois ?? 0;

        // ==================== STATS PAR LIEU DE COMMISSION ====================
        $statsLieuCommission = DB::table('procedures')
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) FILTER (WHERE lieu_commission = 'Organique') as organique,
                COUNT(*) FILTER (WHERE lieu_commission = 'Operation') as operation,
                COUNT(*) FILTER (WHERE lieu_commission IS NULL OR lieu_commission = '') as non_defini
            ")
            ->first();

        // Détail par phase et lieu de commission
        $statsLieuCommissionDetail = DB::table('procedures')
            ->whereNull('deleted_at')
            ->selectRaw("
                lieu_commission,
                phase,
                COUNT(*) as nombre
            ")
            ->groupBy('lieu_commission', 'phase')
            ->orderBy('lieu_commission')
            ->orderBy('phase')
            ->get()
            ->groupBy('lieu_commission')
            ->map(function ($items, $key) {
                return (object) [
                    'lieu' => $key ?: 'Non défini',
                    'total' => $items->sum('nombre'),
                    'phases' => $items->map(function ($item) {
                        return (object) [
                            'phase' => $item->phase ?: 'Sans phase',
                            'nombre' => $item->nombre,
                        ];
                    })->values(),
                ];
            })
            ->values();

        // ==================== STATS CIVILS VS MILITAIRES ====================
        $totalProceduresAvecMilitaire = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->distinct('procedure_militaire.procedure_id')
            ->count('procedure_militaire.procedure_id');

        $totalProceduresAvecCivil = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'civil')
            ->distinct('procedure_militaire.procedure_id')
            ->count('procedure_militaire.procedure_id');

        $totalMilitairesImpliques = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->count();

        $totalCivilsImpliques = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'civil')
            ->count();

        $statsParTypePersonnel = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->select(
                'procedure_militaire.type_personnel',
                DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre_procedures'),
                DB::raw('COUNT(*) as nombre_personnes')
            )
            ->groupBy('procedure_militaire.type_personnel')
            ->get()
            ->map(function ($item) {
                $item->label = $item->type_personnel === 'militaire' ? 'Militaires' : 'Civils';
                $item->icon = $item->type_personnel === 'militaire' ? 'pi pi-users' : 'pi pi-user';
                $item->color = $item->type_personnel === 'militaire' ? 'bg-gpj-500' : 'bg-purple-500';
                $item->bg_color = $item->type_personnel === 'militaire' ? 'bg-gpj-50' : 'bg-purple-50';
                $item->text_color = $item->type_personnel === 'militaire' ? 'text-gpj-700' : 'text-purple-700';
                return $item;
            });

        // ==================== STATS INFRACTIONS PAR TYPE ====================
        $statsInfractionsParType = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select(
                'procedure_militaire.type_personnel',
                DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre')
            )
            ->groupBy('procedure_militaire.type_personnel')
            ->get()
            ->map(function ($item) {
                $item->label = $item->type_personnel === 'militaire' ? 'Militaires' : 'Civils';
                return $item;
            });

        // ==================== STATS FAUTES PAR TYPE ====================
        $statsFautesParType = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select(
                'procedure_militaire.type_personnel',
                DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre')
            )
            ->groupBy('procedure_militaire.type_personnel')
            ->get()
            ->map(function ($item) {
                $item->label = $item->type_personnel === 'militaire' ? 'Militaires' : 'Civils';
                return $item;
            });

        // ==================== TOP INFRACTIONS ====================
        $topInfractions = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select(
                DB::raw("json_array_elements_text(procedure_militaire.infractions) as infraction_id"),
                DB::raw("COUNT(DISTINCT procedure_militaire.procedure_id) as nombre")
            )
            ->groupBy('infraction_id')
            ->orderByDesc('nombre')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $infraction = InfractionBase::find($item->infraction_id);
                return (object) [
                    'id' => $item->infraction_id,
                    'libelle' => $infraction ? $infraction->libelle : 'Infraction #' . $item->infraction_id,
                    'classification' => $infraction ? $infraction->classification : 'Non classée',
                    'nombre' => $item->nombre,
                ];
            });

        $maxInfractions = $topInfractions->max('nombre') ?: 1;

        // ==================== TOP FAUTES AVEC CATÉGORIES ====================
        $toutesFautes = FauteMilitaire::with('categorie')->get()->keyBy('id');
        
        $topFautes = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select(
                DB::raw("json_array_elements_text(procedure_militaire.fautes_militaires) as faute_id"),
                DB::raw("COUNT(DISTINCT procedure_militaire.procedure_id) as nombre")
            )
            ->groupBy('faute_id')
            ->orderByDesc('nombre')
            ->limit(10)
            ->get()
            ->map(function ($item) use ($toutesFautes) {
                $faute = $toutesFautes->get((int)$item->faute_id);
                return (object) [
                    'id' => (int)$item->faute_id,
                    'libelle' => $faute ? $faute->libelle : 'Faute #' . $item->faute_id,
                    'code' => $faute ? $faute->code : null,
                    'categorie' => $faute && $faute->categorie ? $faute->categorie->libelle : 'Non catégorisé',
                    'categorie_id' => $faute && $faute->categorie ? $faute->categorie->id : null,
                    'nombre' => $item->nombre,
                ];
            });

        $maxFautes = $topFautes->max('nombre') ?: 1;

        // ==================== STATS FAUTES PAR CATÉGORIE ====================
        $fautesParCategorie = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select(
                DB::raw("json_array_elements_text(procedure_militaire.fautes_militaires) as faute_id"),
                DB::raw("COUNT(DISTINCT procedure_militaire.procedure_id) as nombre")
            )
            ->groupBy('faute_id')
            ->get()
            ->map(function ($item) use ($toutesFautes) {
                $faute = $toutesFautes->get((int)$item->faute_id);
                return (object) [
                    'faute_id' => (int)$item->faute_id,
                    'categorie_id' => $faute && $faute->categorie ? $faute->categorie->id : null,
                    'categorie_libelle' => $faute && $faute->categorie ? $faute->categorie->libelle : 'Non catégorisé',
                    'faute_libelle' => $faute ? $faute->libelle : 'Faute #' . $item->faute_id,
                    'nombre' => $item->nombre,
                ];
            });

        // Regrouper par catégorie
        $statsFautesParCategorie = $fautesParCategorie
            ->groupBy('categorie_id')
            ->map(function ($items, $categorieId) {
                $first = $items->first();
                return (object) [
                    'categorie_id' => $categorieId,
                    'categorie_libelle' => $first->categorie_libelle,
                    'total' => $items->sum('nombre'),
                    'fautes' => $items->map(function ($item) {
                        return (object) [
                            'faute_id' => $item->faute_id,
                            'libelle' => $item->faute_libelle,
                            'nombre' => $item->nombre,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->values();

        // ==================== STATS PAR ARMÉE (FAUTES) ====================
        $statsFautesParArmee = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('militaires.armee', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS FAUTES PAR CATÉGORIE DE GRADE ====================
        $statsFautesParCategorieGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('categories_grades.libelle', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS FAUTES PAR GRADE ====================
        $statsFautesParGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->limit(15)
            ->get();

        // ==================== STATS FAUTES PAR GENRE ====================
        $totalFautes = DB::table('procedure_militaire')
            ->whereNotNull('fautes_militaires')
            ->whereRaw("json_array_length(fautes_militaires) > 0")
            ->count();

        $statsFautesParGenre = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('militaires.genre', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($totalFautes) {
                $item->pourcentage = $totalFautes > 0 ? round(($item->nombre / $totalFautes) * 100, 1) : 0;
                return $item;
            });

        // ==================== STATS PAR ARMÉE (INFRACTIONS) ====================
        $statsParArmee = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('militaires.armee', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS PAR CATÉGORIE DE GRADE (INFRACTIONS) ====================
        $statsParCategorieGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('categories_grades.libelle', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS PAR GRADE (INFRACTIONS) ====================
        $statsParGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->limit(15)
            ->get();

        // ==================== STATS PAR GENRE (INFRACTIONS) ====================
        $totalProcedures = Procedure::whereNull('deleted_at')->count();
        $statsParGenre = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->where('procedure_militaire.type_personnel', 'militaire')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('militaires.genre', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($totalProcedures) {
                $item->pourcentage = $totalProcedures > 0 ? round(($item->nombre / $totalProcedures) * 100, 1) : 0;
                return $item;
            });

        // ==================== STATS PARQUETS ====================
        $statsParquetDetail = DB::table('parquets')
            ->leftJoin('procedures', function($join) {
                $join->on('parquets.id', '=', 'procedures.parquet_id')
                     ->whereNull('procedures.deleted_at');
            })
            ->select(
                'parquets.id',
                'parquets.nom',
                'parquets.type',
                'parquets.localisation',
                'parquets.code',
                'parquets.is_active',
                DB::raw('COUNT(procedures.id) as nombre_procedures')
            )
            ->groupBy('parquets.id', 'parquets.nom', 'parquets.type', 'parquets.localisation', 'parquets.code', 'parquets.is_active')
            ->orderByDesc('nombre_procedures')
            ->get()
            ->map(function ($item) {
                $item->type_label = $item->type === 'militaire' ? 'Militaire' : 'Droit Commun';
                $item->active_label = $item->is_active ? 'Actif' : 'Inactif';
                return $item;
            });

        $statsParquets = DB::table('procedures')
            ->whereNull('deleted_at')
            ->selectRaw("
                parquet_type,
                COUNT(*) as nombre
            ")
            ->groupBy('parquet_type')
            ->get()
            ->map(function ($item) {
                $item->label = $item->parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun';
                return $item;
            });

        $totalParquets = Parquet::count();
        $totalParquetsMilitaires = Parquet::where('type', 'militaire')->count();
        $totalParquetsDroitCommun = Parquet::where('type', 'droit_commun')->count();

        // ==================== ÉVOLUTION DES PROCÉDURES ====================
        $evolutionProcedures = DB::table('procedures')
            ->whereNull('deleted_at')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mois"),
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(*) FILTER (WHERE phase = 'Cloturee') as cloturees")
            )
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(function ($item) {
                $date = \Carbon\Carbon::createFromFormat('Y-m', $item->mois);
                $item->mois_label = $date->translatedFormat('M Y');
                return $item;
            });

        // ==================== STATS PAR JOUR ====================
        $statsParJour = DB::table('procedures')
            ->whereNull('deleted_at')
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as jour"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('jour')
            ->orderBy('jour')
            ->get()
            ->map(function ($item) {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d', $item->jour);
                $item->jour_label = $date->translatedFormat('D d');
                return $item;
            });

        // ==================== COMPTEURS ====================
        $totalMilitaires = Militaire::count();
        $totalInfractions = InfractionBase::count();
        $totalProceduresEnCours = Procedure::whereNull('deleted_at')->where('phase', '!=', 'Cloturee')->count();

        // ==================== PROCÉDURES RÉCENTES ====================
        $proceduresRecentes = Procedure::with([
                'militaire:id,matricule,nom,prenoms,grade',
                'infractions:id,libelle',
                'parquet'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'statsLieuCommission' => $statsLieuCommission,
            'statsLieuCommissionDetail' => $statsLieuCommissionDetail,
            'topInfractions' => $topInfractions,
            'maxInfractions' => $maxInfractions,
            'topFautes' => $topFautes,
            'maxFautes' => $maxFautes,
            'proceduresRecentes' => $proceduresRecentes,
            'totalMilitaires' => $totalMilitaires,
            'totalInfractions' => $totalInfractions,
            'totalProceduresEnCours' => $totalProceduresEnCours,
            'statsParArmee' => $statsParArmee,
            'statsParCategorieGrade' => $statsParCategorieGrade,
            'statsParGrade' => $statsParGrade,
            'statsParGenre' => $statsParGenre,
            'statsFautesParArmee' => $statsFautesParArmee,
            'statsFautesParCategorieGrade' => $statsFautesParCategorieGrade,
            'statsFautesParGrade' => $statsFautesParGrade,
            'statsFautesParGenre' => $statsFautesParGenre,
            'statsFautesParCategorie' => $statsFautesParCategorie,
            'statsParquets' => $statsParquets,
            'statsParquetDetail' => $statsParquetDetail,
            'totalParquets' => $totalParquets,
            'totalParquetsMilitaires' => $totalParquetsMilitaires,
            'totalParquetsDroitCommun' => $totalParquetsDroitCommun,
            'statsParTypePersonnel' => $statsParTypePersonnel,
            'totalProceduresAvecMilitaire' => $totalProceduresAvecMilitaire,
            'totalProceduresAvecCivil' => $totalProceduresAvecCivil,
            'totalMilitairesImpliques' => $totalMilitairesImpliques,
            'totalCivilsImpliques' => $totalCivilsImpliques,
            'statsInfractionsParType' => $statsInfractionsParType,
            'statsFautesParType' => $statsFautesParType,
            'evolutionProcedures' => $evolutionProcedures,
            'statsParJour' => $statsParJour,
        ]);
    }
}