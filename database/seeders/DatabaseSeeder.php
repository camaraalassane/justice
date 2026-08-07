<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Désactiver les contraintes de clés étrangères pendant le seeding
        Schema::disableForeignKeyConstraints();

        $this->call([
            // Tables indépendantes
            ArmeesSeeder::class,
            UnitesSeeder::class,
            UsersSeeder::class,
            GradesSeeder::class,
            PhaseTypesSeeder::class,
            InfractionsBaseSeeder::class,
            ParquetsSeeder::class,
             CategorieFautesSeeder::class,
            // Tables avec dépendances
            MilitairesSeeder::class,
        ]);

        // Réactiver les contraintes
        Schema::enableForeignKeyConstraints();
    }
}