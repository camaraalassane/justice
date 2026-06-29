<?php

namespace Database\Seeders;

use App\Models\CategorieGrade;
use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradesSeeder extends Seeder
{
    public function run(): void
    {
        // Catégories
        $categories = [
            ['libelle' => 'Militaire du rang', 'ordre' => 1],
            ['libelle' => 'Sous-officier', 'ordre' => 2],
            ['libelle' => 'Officier subalterne', 'ordre' => 3],
            ['libelle' => 'Officier supérieur', 'ordre' => 4],
            ['libelle' => 'Officier général', 'ordre' => 5],
        ];

        foreach ($categories as $cat) {
            CategorieGrade::create($cat);
        }

        // Récupérer les IDs
        $mdr = CategorieGrade::where('libelle', 'Militaire du rang')->first()->id;
        $so = CategorieGrade::where('libelle', 'Sous-officier')->first()->id;
        $osub = CategorieGrade::where('libelle', 'Officier subalterne')->first()->id;
        $osup = CategorieGrade::where('libelle', 'Officier supérieur')->first()->id;
        $og = CategorieGrade::where('libelle', 'Officier général')->first()->id;

        $grades = [
            // Militaire du rang
            ['libelle' => 'Soldat 2', 'abreviation' => 'Sdt2', 'categorie_grade_id' => $mdr, 'ordre' => 1, 'age_limite' => 50],
            ['libelle' => 'Soldat 1', 'abreviation' => 'Sdt1', 'categorie_grade_id' => $mdr, 'ordre' => 2, 'age_limite' => 50],
            ['libelle' => 'Caporal', 'abreviation' => 'Cpl', 'categorie_grade_id' => $mdr, 'ordre' => 3, 'age_limite' => 50],
            ['libelle' => 'Caporal-chef', 'abreviation' => 'Cpl-Chef', 'categorie_grade_id' => $mdr, 'ordre' => 4, 'age_limite' => 50],

            // Sous-officier
            ['libelle' => 'Sergent', 'abreviation' => 'Sgt', 'categorie_grade_id' => $so, 'ordre' => 5, 'age_limite' => 53],
            ['libelle' => 'Sergent-Chef', 'abreviation' => 'Sch', 'categorie_grade_id' => $so, 'ordre' => 6, 'age_limite' => 53],
            ['libelle' => 'Adjudant', 'abreviation' => 'Adj', 'categorie_grade_id' => $so, 'ordre' => 7, 'age_limite' => 56],
            ['libelle' => 'Adjudant-Chef', 'abreviation' => 'AdC', 'categorie_grade_id' => $so, 'ordre' => 8, 'age_limite' => 56],
            ['libelle' => 'Adjudant-Chef major', 'abreviation' => 'ACM', 'categorie_grade_id' => $so, 'ordre' => 9, 'age_limite' => 58],

            // Officier subalterne
            ['libelle' => 'Sous-lieutenant', 'abreviation' => 'SLt', 'categorie_grade_id' => $osub, 'ordre' => 10, 'age_limite' => 60],
            ['libelle' => 'Lieutenant', 'abreviation' => 'LTN', 'categorie_grade_id' => $osub, 'ordre' => 11, 'age_limite' => 60],
            ['libelle' => 'Capitaine', 'abreviation' => 'CNE', 'categorie_grade_id' => $osub, 'ordre' => 12, 'age_limite' => 60],

            // Officier supérieur
            ['libelle' => 'Commandant', 'abreviation' => 'CDT', 'categorie_grade_id' => $osup, 'ordre' => 13, 'age_limite' => 62],
            ['libelle' => 'Lieutenant-colonel', 'abreviation' => 'LCL', 'categorie_grade_id' => $osup, 'ordre' => 14, 'age_limite' => 62],
            ['libelle' => 'Colonel', 'abreviation' => 'COL', 'categorie_grade_id' => $osup, 'ordre' => 15, 'age_limite' => 62],
            ['libelle' => 'Colonel-Major', 'abreviation' => 'CLM', 'categorie_grade_id' => $osup, 'ordre' => 16, 'age_limite' => 62],

            // Officier général
            ['libelle' => 'Général de brigade', 'abreviation' => 'GBR', 'categorie_grade_id' => $og, 'ordre' => 17, 'age_limite' => 65],
            ['libelle' => 'Général de division', 'abreviation' => 'GDV', 'categorie_grade_id' => $og, 'ordre' => 18, 'age_limite' => 65],
            ['libelle' => 'Général de corps d\'armée', 'abreviation' => 'GCA', 'categorie_grade_id' => $og, 'ordre' => 19, 'age_limite' => 65],
            ['libelle' => 'Général d\'armée', 'abreviation' => 'GAR', 'categorie_grade_id' => $og, 'ordre' => 20, 'age_limite' => 65],
        ];

        foreach ($grades as $grade) {
            Grade::create($grade);
        }
    }
}