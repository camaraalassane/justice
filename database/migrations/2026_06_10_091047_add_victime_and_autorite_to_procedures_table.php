<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Autorité compétente
            $table->enum('autorite_competente', ['BAMAKO', 'MOPTI', 'GAO', 'KAYES'])->nullable();
            $table->date('date_ouverture')->nullable();
        });

        Schema::create('victimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->string('nom');
            $table->string('prenom');
            $table->string('profession')->nullable();
            $table->timestamps();
        });

        // Modifier la table historique_phases pour ajouter type_document et date_document
        Schema::table('historique_phases', function (Blueprint $table) {
            $table->string('type_document')->nullable()->after('phase_apres');
            $table->date('date_document')->nullable()->after('type_document');
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn('autorite_competente');
            $table->dropColumn('date_ouverture');
        });

        Schema::dropIfExists('victimes');

        Schema::table('historique_phases', function (Blueprint $table) {
            $table->dropColumn('type_document');
            $table->dropColumn('date_document');
        });
    }
};