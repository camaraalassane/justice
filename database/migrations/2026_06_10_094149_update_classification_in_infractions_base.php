<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mettre à jour les valeurs existantes
        DB::statement("UPDATE infractions_base SET classification = 'Criminelle' WHERE classification = 'Classe A'");
        DB::statement("UPDATE infractions_base SET classification = 'Délictuelle' WHERE classification = 'Classe B'");
        DB::statement("UPDATE infractions_base SET classification = 'Contravention' WHERE classification = 'Classe C'");
    }

    public function down(): void
    {
        DB::statement("UPDATE infractions_base SET classification = 'Classe A' WHERE classification = 'Criminelle'");
        DB::statement("UPDATE infractions_base SET classification = 'Classe B' WHERE classification = 'Délictuelle'");
        DB::statement("UPDATE infractions_base SET classification = 'Classe C' WHERE classification = 'Contravention'");
    }
};