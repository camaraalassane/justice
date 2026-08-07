<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter les colonnes si elles n'existent pas
        Schema::table('militaires', function (Blueprint $table) {
            if (!Schema::hasColumn('militaires', 'lieu_naissance')) {
                $table->string('lieu_naissance')->nullable()->after('date_naissance');
            }
            if (!Schema::hasColumn('militaires', 'nom_pere')) {
                $table->string('nom_pere')->nullable()->after('lieu_naissance');
            }
            if (!Schema::hasColumn('militaires', 'prenoms_pere')) {
                $table->string('prenoms_pere')->nullable()->after('nom_pere');
            }
            if (!Schema::hasColumn('militaires', 'nom_mere')) {
                $table->string('nom_mere')->nullable()->after('prenoms_pere');
            }
            if (!Schema::hasColumn('militaires', 'prenoms_mere')) {
                $table->string('prenoms_mere')->nullable()->after('nom_mere');
            }
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropColumn([
                'lieu_naissance',
                'nom_pere',
                'prenoms_pere',
                'nom_mere',
                'prenoms_mere'
            ]);
        });
    }
};