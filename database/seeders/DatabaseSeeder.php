<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
                UnitesSeeder::class,
        UsersSeeder::class,
        GradesSeeder::class,
        PhaseTypesSeeder::class,
        InfractionsBaseSeeder::class,
        MilitairesSeeder::class,
        ]);
    }
}