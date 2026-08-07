<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            // Ajouter le type de personnel
            if (!Schema::hasColumn('militaires', 'type_personnel')) {
                $table->enum('type_personnel', ['militaire', 'civil'])->default('militaire')->after('id');
            }
            
            // Profession pour les civils
            if (!Schema::hasColumn('militaires', 'profession')) {
                $table->string('profession')->nullable()->after('prenoms');
            }
            
            // Rendre certaines colonnes nullable pour les civils
            // Ces colonnes sont déjà nullable, mais on s'assure
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropColumn(['type_personnel', 'profession']);
        });
    }
};