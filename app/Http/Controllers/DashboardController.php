<?php

namespace App\Http\Controllers;

use App\Models\Procedure;
use App\Models\Militaire;
use App\Models\InfractionBase;
use App\Models\ProcedureMilitaire;
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

        // ==================== TOP INFRACTIONS ====================
        // Extraire les infractions de la table procedure_militaire (champ JSON)
        // Utiliser json_array_elements_text pour PostgreSQL (fonctionne avec json et jsonb)
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
                // Récupérer le libellé de l'infraction
                $infraction = InfractionBase::find($item->infraction_id);
                return (object) [
                    'id' => $item->infraction_id,
                    'libelle' => $infraction ? $infraction->libelle : 'Infraction #' . $item->infraction_id,
                    'classification' => $infraction ? $infraction->classification : 'Non classée',
                    'nombre' => $item->nombre,
                ];
            });

        $maxInfractions = $topInfractions->max('nombre') ?: 1;

        // ==================== TOP FAUTES ====================
        // Extraire les fautes de la table procedure_militaire (champ JSON)
        $topFautes = DB::table('procedure_militaire')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select(
                DB::raw("json_array_elements_text(procedure_militaire.fautes_militaires) as faute_json"),
                DB::raw("COUNT(DISTINCT procedure_militaire.procedure_id) as nombre")
            )
            ->groupBy('faute_json')
            ->orderByDesc('nombre')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                // Décoder le JSON de la faute
                $faute = json_decode($item->faute_json);
                return (object) [
                    'libelle' => $faute->libelle ?? 'Faute non définie',
                    'nombre' => $item->nombre,
                ];
            });

        $maxFautes = $topFautes->max('nombre') ?: 1;

        // ==================== STATS PAR ARMÉE (Infractions) ====================
        $statsParArmee = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('militaires.armee', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS PAR CATÉGORIE DE GRADE (Infractions) ====================
        $statsParCategorieGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('categories_grades.libelle', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get();

        // ==================== STATS PAR GRADE (Infractions) ====================
        $statsParGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->limit(15)
            ->get();

        // ==================== STATS PAR GENRE (Infractions) ====================
        $totalProcedures = Procedure::whereNull('deleted_at')->count();
        $statsParGenre = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->whereNotNull('procedure_militaire.infractions')
            ->whereRaw("json_array_length(procedure_militaire.infractions) > 0")
            ->select('militaires.genre', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($totalProcedures) {
                $item->pourcentage = $totalProcedures > 0 ? round(($item->nombre / $totalProcedures) * 100, 1) : 0;
                return $item;
            });

        // ==================== STATS FAUTES MILITAIRES ====================
        $statsFautesParArmee = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('militaires.armee', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get();

        $statsFautesParCategorieGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('categories_grades.libelle', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get();

        $statsFautesParGrade = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->limit(15)
            ->get();

        $totalFautes = DB::table('procedure_militaire')
            ->whereNotNull('fautes_militaires')
            ->whereRaw("json_array_length(fautes_militaires) > 0")
            ->count();

        $statsFautesParGenre = DB::table('procedure_militaire')
            ->join('militaires', 'procedure_militaire.militaire_id', '=', 'militaires.id')
            ->join('procedures', 'procedure_militaire.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->whereNotNull('procedure_militaire.fautes_militaires')
            ->whereRaw("json_array_length(procedure_militaire.fautes_militaires) > 0")
            ->select('militaires.genre', DB::raw('COUNT(DISTINCT procedure_militaire.procedure_id) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($totalFautes) {
                $item->pourcentage = $totalFautes > 0 ? round(($item->nombre / $totalFautes) * 100, 1) : 0;
                return $item;
            });

        // ==================== COMPTEURS ====================
        $totalMilitaires = Militaire::count();
        $totalInfractions = InfractionBase::count();
        $totalProceduresEnCours = Procedure::whereNull('deleted_at')->where('phase', '!=', 'Cloturee')->count();

        // ==================== PROCÉDURES RÉCENTES ====================
        $proceduresRecentes = Procedure::with(['militaire:id,matricule,nom,prenoms,grade', 'infractions:id,libelle'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
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
        ]);
    }
}