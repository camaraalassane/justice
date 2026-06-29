<?php

namespace Database\Seeders;

use App\Models\Militaire;
use Illuminate\Database\Seeder;

class MilitairesSeeder extends Seeder
{
    public function run(): void
    {
        $militaires = [
            [
                'matricule' => 'MIL-2020-001',
                'nom' => 'Traoré',
                'prenoms' => 'Moussa',
                'date_naissance' => '1990-05-15',
                'grade_id' => 5, // Sergent
                'unite' => '1ère Compagnie',
                'adresse' => 'Bamako, Quartier 123',
                'telephone' => '223 12 34 56 78',
                'statut' => 'Actif',
                'genre' => 'Masculin',
                'armee' => 'Armée de Terre',
            ],
            [
                'matricule' => 'MIL-2020-002',
                'nom' => 'Coulibaly',
                'prenoms' => 'Amadou',
                'date_naissance' => '1992-08-20',
                'grade_id' => 3, // Caporal
                'unite' => '2ème Compagnie',
                'adresse' => 'Kati, Camp Militaire',
                'telephone' => '223 87 65 43 21',
                'statut' => 'Actif',
                'genre' => 'Masculin',
                'armee' => 'Armée de Terre',
            ],
            [
                'matricule' => 'MIL-2021-003',
                'nom' => 'Keita',
                'prenoms' => 'Ibrahim',
                'date_naissance' => '1995-03-10',
                'grade_id' => 2, // Soldat 1
                'unite' => '3ème Compagnie',
                'adresse' => 'Ségou, Quartier Administratif',
                'telephone' => '223 44 55 66 77',
                'statut' => 'Suspendu',
                'genre' => 'Masculin',
                'armee' => 'Garde Nationale',
            ],
            [
                'matricule' => 'MIL-2019-004',
                'nom' => 'Diallo',
                'prenoms' => 'Fatoumata',
                'date_naissance' => '1988-12-01',
                'grade_id' => 6, // Sergent-Chef
                'unite' => 'État-Major Général',
                'adresse' => 'Bamako, ACI 2000',
                'telephone' => '223 99 88 77 66',
                'statut' => 'Actif',
                'genre' => 'Féminin',
                'armee' => 'État-Major Général',
            ],
            [
                'matricule' => 'MIL-2021-005',
                'nom' => 'Koné',
                'prenoms' => 'Seydou',
                'date_naissance' => '1996-07-25',
                'grade_id' => 1, // Soldat 2
                'unite' => 'Bataillon de Kati',
                'adresse' => 'Kati, Quartier Est',
                'telephone' => '223 33 22 11 00',
                'statut' => 'Déserteur',
                'genre' => 'Masculin',
                'armee' => 'Armée de Terre',
            ],
            [
                'matricule' => 'MIL-2018-006',
                'nom' => 'Sangaré',
                'prenoms' => 'Oumar',
                'date_naissance' => '1985-11-15',
                'grade_id' => 7, // Adjudant
                'unite' => 'Direction du Génie Militaire',
                'adresse' => 'Mopti, Centre-Ville',
                'telephone' => '223 55 44 33 22',
                'statut' => 'Actif',
                'genre' => 'Masculin',
                'armee' => 'Direction du Génie Militaire',
            ],
            [
                'matricule' => 'MIL-2022-007',
                'nom' => 'Maïga',
                'prenoms' => 'Aïssata',
                'date_naissance' => '1993-04-08',
                'grade_id' => 3, // Caporal
                'unite' => 'Gendarmerie Nationale',
                'adresse' => 'Gao, Quartier Nord',
                'telephone' => '223 77 66 55 44',
                'statut' => 'Actif',
                'genre' => 'Féminin',
                'armee' => 'Gendarmerie Nationale',
            ],
            [
                'matricule' => 'MIL-2017-008',
                'nom' => 'Doumbia',
                'prenoms' => 'Mamadou',
                'date_naissance' => '1980-09-30',
                'grade_id' => 11, // Lieutenant
                'unite' => 'Direction du Service de Santé',
                'adresse' => 'Kayes, Quartier Sud',
                'telephone' => '223 66 77 88 99',
                'statut' => 'Radié',
                'genre' => 'Masculin',
                'armee' => 'Direction du Service de Santé des Armées',
            ],
            [
                'matricule' => 'MIL-2023-009',
                'nom' => 'Camara',
                'prenoms' => 'Boubacar',
                'date_naissance' => '1998-02-14',
                'grade_id' => 1, // Soldat 2
                'unite' => 'Direction des Transmissions',
                'adresse' => 'Bamako, Faladiè',
                'telephone' => '223 88 77 66 55',
                'statut' => 'Actif',
                'genre' => 'Masculin',
                'armee' => 'Direction des Transmissions',
            ],
            [
                'matricule' => 'MIL-2020-010',
                'nom' => 'Sidibé',
                'prenoms' => 'Kadiatou',
                'date_naissance' => '1991-06-18',
                'grade_id' => 5, // Sergent
                'unite' => 'Police Nationale',
                'adresse' => 'Bamako, Hippodrome',
                'telephone' => '223 99 00 11 22',
                'statut' => 'Actif',
                'genre' => 'Féminin',
                'armee' => 'Police Nationale',
            ],
        ];

        foreach ($militaires as $militaire) {
            Militaire::create($militaire);
        }
    }
}