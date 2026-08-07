<?php

namespace Database\Seeders;

use App\Models\Parquet;
use Illuminate\Database\Seeder;

class ParquetsSeeder extends Seeder
{
    public function run(): void
    {
        // Parquets Militaires (existants)
        $parquetsMilitaires = [
            ['nom' => 'BAMAKO', 'code' => 'BMK', 'localisation' => 'Bamako'],
            ['nom' => 'MOPTI', 'code' => 'MOP', 'localisation' => 'Mopti'],
            ['nom' => 'GAO', 'code' => 'GAO', 'localisation' => 'Gao'],
            ['nom' => 'KAYES', 'code' => 'KAY', 'localisation' => 'Kayes'],
        ];

        foreach ($parquetsMilitaires as $p) {
            Parquet::firstOrCreate(
                ['nom' => $p['nom'], 'type' => 'militaire'],
                [
                    'localisation' => $p['localisation'],
                    'code' => $p['code'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ ' . count($parquetsMilitaires) . ' parquets militaires créés.');
    }
}