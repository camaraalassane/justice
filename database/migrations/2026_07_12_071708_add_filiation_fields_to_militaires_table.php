<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            // Nouveaux champs pour l'état civil
            $table->string('lieu_naissance')->nullable()->after('date_naissance');
            
            // Filiation - Père
            $table->string('nom_pere')->nullable()->after('lieu_naissance');
            $table->string('prenoms_pere')->nullable()->after('nom_pere');
            
            // Filiation - Mère
            $table->string('nom_mere')->nullable()->after('prenoms_pere');
            $table->string('prenoms_mere')->nullable()->after('nom_mere');
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