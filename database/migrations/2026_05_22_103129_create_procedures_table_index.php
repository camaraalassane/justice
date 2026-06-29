<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->index(['phase', 'statut_jugement']);
            $table->index(['militaire_id', 'phase']);
            $table->index('date_ordre_poursuite');
            $table->index('date_cloture');
        });

        // Index Full-Text PostgreSQL sur les militaires
        DB::statement("CREATE INDEX IF NOT EXISTS idx_militaires_recherche ON militaires USING gin(to_tsvector('french', nom || ' ' || prenoms || ' ' || matricule))");
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropIndex(['phase', 'statut_jugement']);
            $table->dropIndex(['militaire_id', 'phase']);
            $table->dropIndex(['date_ordre_poursuite']);
            $table->dropIndex(['date_cloture']);
        });

        DB::statement('DROP INDEX IF EXISTS idx_militaires_recherche');
    }
};