<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE militaires DROP CONSTRAINT IF EXISTS militaires_statut_check');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE militaires ADD CONSTRAINT militaires_statut_check CHECK (statut IN ('Actif', 'Inactif', 'Retraité', 'Radié'))");
    }
};