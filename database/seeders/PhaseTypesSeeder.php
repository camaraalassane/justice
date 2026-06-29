<?php

namespace Database\Seeders;

use App\Models\PhaseType;
use Illuminate\Database\Seeder;

class PhaseTypesSeeder extends Seeder
{
    public function run(): void
    {
        $phases = [
            ['libelle' => 'Ordre de Poursuite', 'slug' => 'ordre_de_poursuite', 'is_system' => true, 'ordre' => 1],
            ['libelle' => 'Mise à Disposition', 'slug' => 'mise_a_disposition', 'is_system' => true, 'ordre' => 2],
            ['libelle' => 'Communiqué', 'slug' => 'communique', 'is_system' => true, 'ordre' => 3],
        ];

        foreach ($phases as $phase) {
            PhaseType::create($phase);
        }
    }
}