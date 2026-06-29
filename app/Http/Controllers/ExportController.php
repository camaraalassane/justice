<?php

namespace App\Http\Controllers;

use App\Exports\StatsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    // ==================== TOP ====================

    public function topInfractions()
    {
        $data = DB::table('infractions_base')
            ->join('procedure_infraction', 'infractions_base.id', '=', 'procedure_infraction.infraction_base_id')
            ->join('procedures', 'procedure_infraction.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->select('infractions_base.libelle', 'infractions_base.classification', DB::raw('COUNT(*) as nombre'))
            ->groupBy('infractions_base.libelle', 'infractions_base.classification')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['libelle' => $item->libelle, 'classification' => $item->classification, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Top Infractions', ['Libellé', 'Classification', 'Nombre']),
            'top-infractions.xlsx'
        );
    }

    public function topFautes()
    {
        $data = DB::table('fautes_militaires')
            ->join('procedures', 'fautes_militaires.procedure_id', '=', 'procedures.id')
            ->whereNull('procedures.deleted_at')
            ->select('fautes_militaires.libelle', DB::raw('COUNT(*) as nombre'))
            ->groupBy('fautes_militaires.libelle')
            ->orderByDesc('nombre')
            ->limit(20)
            ->get()
            ->map(fn($item) => ['libelle' => $item->libelle, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Top Fautes Militaires', ['Libellé', 'Nombre']),
            'top-fautes-militaires.xlsx'
        );
    }

    // ==================== INFRACTIONS ====================

    public function infractionsParArmee()
    {
        $data = DB::table('procedures')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->select('militaires.armee', DB::raw('COUNT(*) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['armee' => $item->armee, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Infractions par Armée', ['Armée', 'Nombre']),
            'infractions-par-armee.xlsx'
        );
    }

    public function infractionsParCategorieGrade()
    {
        $data = DB::table('procedures')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->whereNull('procedures.deleted_at')
            ->select('categories_grades.libelle', DB::raw('COUNT(*) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['categorie' => $item->libelle, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Infractions par Catégorie', ['Catégorie', 'Nombre']),
            'infractions-par-categorie-grade.xlsx'
        );
    }

    public function infractionsParGrade()
    {
        $data = DB::table('procedures')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->whereNull('procedures.deleted_at')
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(*) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['grade' => $item->libelle, 'abreviation' => $item->abreviation, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Infractions par Grade', ['Grade', 'Abréviation', 'Nombre']),
            'infractions-par-grade.xlsx'
        );
    }

    public function infractionsParGenre()
    {
        $total = DB::table('procedures')->whereNull('deleted_at')->count();
        $data = DB::table('procedures')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->select('militaires.genre', DB::raw('COUNT(*) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'genre' => $item->genre,
                    'nombre' => $item->nombre,
                    'pourcentage' => $total > 0 ? round(($item->nombre / $total) * 100, 1) . '%' : '0%',
                ];
            });

        return Excel::download(
            new StatsExport($data, 'Infractions par Genre', ['Genre', 'Nombre', 'Pourcentage']),
            'infractions-par-genre.xlsx'
        );
    }

    // ==================== FAUTES ====================

    public function fautesParArmee()
    {
        $data = DB::table('fautes_militaires')
            ->join('procedures', 'fautes_militaires.procedure_id', '=', 'procedures.id')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.armee')
            ->select('militaires.armee', DB::raw('COUNT(*) as nombre'))
            ->groupBy('militaires.armee')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['armee' => $item->armee, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Fautes par Armée', ['Armée', 'Nombre']),
            'fautes-par-armee.xlsx'
        );
    }

    public function fautesParCategorieGrade()
    {
        $data = DB::table('fautes_militaires')
            ->join('procedures', 'fautes_militaires.procedure_id', '=', 'procedures.id')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->join('categories_grades', 'grades.categorie_grade_id', '=', 'categories_grades.id')
            ->whereNull('procedures.deleted_at')
            ->select('categories_grades.libelle', DB::raw('COUNT(*) as nombre'))
            ->groupBy('categories_grades.libelle')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['categorie' => $item->libelle, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Fautes par Catégorie', ['Catégorie', 'Nombre']),
            'fautes-par-categorie-grade.xlsx'
        );
    }

    public function fautesParGrade()
    {
        $data = DB::table('fautes_militaires')
            ->join('procedures', 'fautes_militaires.procedure_id', '=', 'procedures.id')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->join('grades', 'militaires.grade_id', '=', 'grades.id')
            ->whereNull('procedures.deleted_at')
            ->select('grades.libelle', 'grades.abreviation', DB::raw('COUNT(*) as nombre'))
            ->groupBy('grades.libelle', 'grades.abreviation')
            ->orderByDesc('nombre')
            ->get()
            ->map(fn($item) => ['grade' => $item->libelle, 'abreviation' => $item->abreviation, 'nombre' => $item->nombre]);

        return Excel::download(
            new StatsExport($data, 'Fautes par Grade', ['Grade', 'Abréviation', 'Nombre']),
            'fautes-par-grade.xlsx'
        );
    }

    public function fautesParGenre()
    {
        $total = DB::table('fautes_militaires')->count();
        $data = DB::table('fautes_militaires')
            ->join('procedures', 'fautes_militaires.procedure_id', '=', 'procedures.id')
            ->join('militaires', 'procedures.militaire_id', '=', 'militaires.id')
            ->whereNull('procedures.deleted_at')
            ->whereNotNull('militaires.genre')
            ->select('militaires.genre', DB::raw('COUNT(*) as nombre'))
            ->groupBy('militaires.genre')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'genre' => $item->genre,
                    'nombre' => $item->nombre,
                    'pourcentage' => $total > 0 ? round(($item->nombre / $total) * 100, 1) . '%' : '0%',
                ];
            });

        return Excel::download(
            new StatsExport($data, 'Fautes par Genre', ['Genre', 'Nombre', 'Pourcentage']),
            'fautes-par-genre.xlsx'
        );
    }
}