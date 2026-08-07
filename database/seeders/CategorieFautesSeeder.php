<?php

namespace Database\Seeders;

use App\Models\CategorieFaute;
use App\Models\FauteMilitaire;
use Illuminate\Database\Seeder;

class CategorieFautesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'libelle' => 'Manquement à la discipline',
                'description' => 'Fautes liées au non-respect des règles de discipline militaire',
                'ordre' => 1,
                'fautes' => [
                    ['libelle' => 'Insoumission', 'code' => 'F-001'],
                    ['libelle' => 'Refus d\'obéissance', 'code' => 'F-002'],
                    ['libelle' => 'Manquement au respect hiérarchique', 'code' => 'F-003'],
                ]
            ],
            [
                'libelle' => 'Infraction au règlement intérieur',
                'description' => 'Fautes liées au non-respect du règlement intérieur',
                'ordre' => 2,
                'fautes' => [
                    ['libelle' => 'Non-respect des horaires', 'code' => 'F-004'],
                    ['libelle' => 'Absence non justifiée', 'code' => 'F-005'],
                ]
            ],
            [
                'libelle' => 'Fautes de service',
                'description' => 'Fautes commises dans l\'exercice des fonctions',
                'ordre' => 3,
                'fautes' => [
                    ['libelle' => 'Négligence dans le service', 'code' => 'F-006'],
                    ['libelle' => 'Mauvaise exécution des ordres', 'code' => 'F-007'],
                ]
            ],
            [
                'libelle' => 'Manquement à l\'honneur',
                'description' => 'Fautes portant atteinte à l\'honneur militaire',
                'ordre' => 4,
                'fautes' => [
                    ['libelle' => 'Mensonge', 'code' => 'F-008'],
                    ['libelle' => 'Falsification de documents', 'code' => 'F-009'],
                ]
            ],
            [
                'libelle' => 'Fautes graves',
                'description' => 'Fautes de gravité exceptionnelle',
                'ordre' => 5,
                'fautes' => [
                    ['libelle' => 'Désertion', 'code' => 'F-010'],
                    ['libelle' => 'Trahison', 'code' => 'F-011'],
                ]
            ],
            [
                'libelle' => 'Fautes légères',
                'description' => 'Fautes de gravité mineure',
                'ordre' => 6,
                'fautes' => [
                    ['libelle' => 'Retard', 'code' => 'F-012'],
                    ['libelle' => 'Tenue non réglementaire', 'code' => 'F-013'],
                ]
            ],
            [
                'libelle' => 'Autres fautes',
                'description' => 'Fautes ne correspondant pas aux autres catégories',
                'ordre' => 7,
                'fautes' => []
            ],
        ];

        foreach ($categories as $catData) {
            $categorie = CategorieFaute::create([
                'libelle' => $catData['libelle'],
                'description' => $catData['description'],
                'ordre' => $catData['ordre'],
            ]);

            foreach ($catData['fautes'] as $fauteData) {
                FauteMilitaire::create([
                    'categorie_faute_id' => $categorie->id,
                    'libelle' => $fauteData['libelle'],
                    'code' => $fauteData['code'],
                    'ordre' => count($catData['fautes']),
                ]);
            }
        }
    }
}