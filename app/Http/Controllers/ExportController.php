<?php

namespace App\Http\Controllers;

use App\Exports\StatsExport;
use App\Models\Procedure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    // ==================== EXPORT LISTE PROCÉDURES ====================

    /**
     * Exporter la liste des procédures en Excel avec filtres
     */
    public function exportProceduresListe(Request $request)
    {
        $query = Procedure::with(['militaire', 'procedureMilitaires.militaire', 'parquet']);

        // Filtres
        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_procedure', 'ILIKE', "%{$search}%")
                  ->orWhereHas('militaire', function($milQ) use ($search) {
                      $milQ->where('nom', 'ILIKE', "%{$search}%")
                           ->orWhere('prenoms', 'ILIKE', "%{$search}%")
                           ->orWhere('matricule', 'ILIKE', "%{$search}%")
                           ->orWhere('profession', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('procedureMilitaires.militaire', function($milQ) use ($search) {
                      $milQ->where('nom', 'ILIKE', "%{$search}%")
                           ->orWhere('prenoms', 'ILIKE', "%{$search}%")
                           ->orWhere('matricule', 'ILIKE', "%{$search}%")
                           ->orWhere('profession', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('parquet', function($pq) use ($search) {
                      $pq->where('nom', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('mois')) {
            $query->whereMonth('date_ouverture', $request->mois);
        }

        if ($request->filled('annee')) {
            $query->whereYear('date_ouverture', $request->annee);
        }

        if ($request->filled('jour')) {
            $query->whereDate('date_ouverture', $request->jour);
        }

        if ($request->filled('type_personnel')) {
            $query->whereHas('procedureMilitaires', function($q) use ($request) {
                $q->where('type_personnel', $request->type_personnel);
            });
        }

        $procedures = $query->orderBy('created_at', 'desc')->get();

        // Créer le fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = [
            'N° Procédure',
            'Date ouverture',
            'Phase',
            'Type personnel',
            'Nom complet',
            'Matricule',
            'Grade / Profession',
            'Parquet',
            'Lieu commission',
            'Statut'
        ];

        foreach ($headers as $index => $header) {
            $col = chr(65 + $index); // A, B, C, ...
            $sheet->setCellValue($col . '1', $header);
        }

        // Style des en-têtes
        $sheet->getStyle('A1:' . chr(65 + count($headers) - 1) . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . chr(65 + count($headers) - 1) . '1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('2d5a3d');
        $sheet->getStyle('A1:' . chr(65 + count($headers) - 1) . '1')->getFont()->getColor()->setARGB('ffffff');

        // Données
        $row = 2;
        foreach ($procedures as $procedure) {
            $firstPm = $procedure->procedureMilitaires->first();
            $typePersonnel = $firstPm?->type_personnel ?? 'N/A';
            $nomComplet = $firstPm?->nom_complet ?? '-';
            $matricule = $firstPm?->militaire?->matricule ?? '-';
            $gradeProfession = $firstPm?->militaire?->grade ?? $firstPm?->militaire?->profession ?? '-';
            $statut = $firstPm?->militaire?->statut ?? '-';

            $sheet->setCellValue('A' . $row, $procedure->numero_procedure ?? '-');
            $sheet->setCellValue('B' . $row, $procedure->date_ouverture?->format('d/m/Y') ?? '-');
            $sheet->setCellValue('C' . $row, $procedure->phase ?? '-');
            $sheet->setCellValue('D' . $row, $typePersonnel === 'militaire' ? 'Militaire' : 'Civil');
            $sheet->setCellValue('E' . $row, $nomComplet);
            $sheet->setCellValue('F' . $row, $matricule);
            $sheet->setCellValue('G' . $row, $gradeProfession);
            $sheet->setCellValue('H' . $row, $procedure->parquet?->nom ?? '-');
            $sheet->setCellValue('I' . $row, $procedure->lieu_commission ?? '-');
            $sheet->setCellValue('J' . $row, $statut);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', chr(65 + count($headers) - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Générer le fichier
        $filename = 'liste_procedures_' . date('Y-m-d_H-i') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}