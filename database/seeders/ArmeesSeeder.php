<?php

namespace Database\Seeders;

use App\Models\Armee;
use Illuminate\Database\Seeder;

class ArmeesSeeder extends Seeder
{
    public function run(): void
    {
        $armees = [
            ['nom' => 'Armée de Terre', 'code' => 'AT'],
            ['nom' => 'Armée de l\'Air', 'code' => 'AA'],
            ['nom' => 'Garde Nationale', 'code' => 'GN'],
            ['nom' => 'Gendarmerie Nationale', 'code' => 'GEN'],
            ['nom' => 'Police Nationale', 'code' => 'PN'],
            ['nom' => 'Protection Civile', 'code' => 'PC'],
            ['nom' => 'Direction du Génie Militaire', 'code' => 'DGM'],
            ['nom' => 'Direction du Service de Santé des Armées', 'code' => 'DSSA'],
            ['nom' => 'Direction du Matériel', 'code' => 'DM'],
            ['nom' => 'Direction des Transmissions', 'code' => 'DT'],
            ['nom' => 'État-Major Général', 'code' => 'EMG'],
            ['nom' => 'DRHA', 'code' => 'DRHA'],
        ];

        foreach ($armees as $armee) {
            Armee::create($armee);
        }
    }
}