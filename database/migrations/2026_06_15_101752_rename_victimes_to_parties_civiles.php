<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renommer la table
        Schema::rename('victimes', 'parties_civiles');

        // Ajouter la colonne adresse
        Schema::table('parties_civiles', function (Blueprint $table) {
            $table->string('adresse')->nullable()->after('profession');
        });

        // Ajouter la colonne numero_document dans historique_phases
        Schema::table('historique_phases', function (Blueprint $table) {
            $table->string('numero_document')->nullable()->after('type_document');
        });
    }

    public function down(): void
    {
        Schema::table('historique_phases', function (Blueprint $table) {
            $table->dropColumn('numero_document');
        });

        Schema::table('parties_civiles', function (Blueprint $table) {
            $table->dropColumn('adresse');
        });

        Schema::rename('parties_civiles', 'victimes');
    }
};