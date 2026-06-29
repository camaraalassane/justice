<?php

namespace Database\Seeders;

use App\Models\Unite;
use Illuminate\Database\Seeder;

class UnitesSeeder extends Seeder
{
    public function run(): void
    {
        // État-Major (niveau 0)
        $em = Unite::create([
            'code_unite' => 'EM-001',
            'nom_unite' => 'État-Major Général',
            'type_unite' => 'État-Major',
            'unite_parent_id' => null,
            'localisation' => 'Bamako',
        ]);

        // Divisions (niveau 1)
        $div1 = Unite::create([
            'code_unite' => 'DIV-001',
            'nom_unite' => '1ère Division d\'Infanterie',
            'type_unite' => 'Division',
            'unite_parent_id' => $em->id,
            'localisation' => 'Kati',
        ]);

        $div2 = Unite::create([
            'code_unite' => 'DIV-002',
            'nom_unite' => '2ème Division d\'Infanterie',
            'type_unite' => 'Division',
            'unite_parent_id' => $em->id,
            'localisation' => 'Ségou',
        ]);

        // Régiments (niveau 2)
        $reg1 = Unite::create([
            'code_unite' => 'REG-001',
            'nom_unite' => '1er Régiment d\'Infanterie',
            'type_unite' => 'Régiment',
            'unite_parent_id' => $div1->id,
            'localisation' => 'Kati',
        ]);

        $reg2 = Unite::create([
            'code_unite' => 'REG-002',
            'nom_unite' => '2ème Régiment d\'Infanterie',
            'type_unite' => 'Régiment',
            'unite_parent_id' => $div1->id,
            'localisation' => 'Kati',
        ]);

        // Bataillons (niveau 3)
        $bat1 = Unite::create([
            'code_unite' => 'BAT-001',
            'nom_unite' => '1er Bataillon',
            'type_unite' => 'Bataillon',
            'unite_parent_id' => $reg1->id,
            'localisation' => 'Kati',
        ]);

        $bat2 = Unite::create([
            'code_unite' => 'BAT-002',
            'nom_unite' => '2ème Bataillon',
            'type_unite' => 'Bataillon',
            'unite_parent_id' => $reg1->id,
            'localisation' => 'Kati',
        ]);

        // Compagnies (niveau 4)
        Unite::create([
            'code_unite' => 'CIE-001',
            'nom_unite' => '1ère Compagnie',
            'type_unite' => 'Compagnie',
            'unite_parent_id' => $bat1->id,
            'localisation' => 'Kati',
        ]);

        Unite::create([
            'code_unite' => 'CIE-002',
            'nom_unite' => '2ème Compagnie',
            'type_unite' => 'Compagnie',
            'unite_parent_id' => $bat1->id,
            'localisation' => 'Kati',
        ]);

        Unite::create([
            'code_unite' => 'CIE-003',
            'nom_unite' => '3ème Compagnie',
            'type_unite' => 'Compagnie',
            'unite_parent_id' => $bat2->id,
            'localisation' => 'Kati',
        ]);
    }
}