<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer la contrainte CHECK sur la colonne phase
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE procedures DROP CONSTRAINT IF EXISTS procedures_phase_check");
        }
    }

    public function down(): void
    {
        // Recréer la contrainte (optionnel)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE procedures ADD CONSTRAINT procedures_phase_check CHECK (phase IN ('Brouillon','DOE','Ordre_de_Poursuite','Mise_a_Disposition','Communique','Jugement','Cloturee'))");
        }
    }
};