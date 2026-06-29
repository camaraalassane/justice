<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_procedure')->unique(); // Format: GPJ-2026-XXXX
            $table->foreignId('militaire_id')->constrained('militaires');
            $table->string('reference_doe')->nullable();
            $table->text('motif_doe')->nullable();
            $table->enum('phase', [
                'Brouillon',
                'DOE',
                'Ordre_de_Poursuite',
                'Mise_a_Disposition',
                'Communique',
                'Jugement',
                'Cloturee'
            ])->default('Brouillon');
            $table->enum('statut_jugement', [
                'En_attente',
                'Condamne',
                'Acquitte',
                'Non_lieu',
                'Classe_sans_suite'
            ])->default('En_attente');
            $table->timestamp('date_ordre_poursuite')->nullable();
            $table->timestamp('date_mise_disposition')->nullable();
            $table->foreignId('unite_mad_id')->nullable()->constrained('unites');
            $table->timestamp('date_notification')->nullable();
            $table->foreignId('cree_par')->constrained('users');
            $table->foreignId('valide_par')->nullable()->constrained('users');
            $table->timestamp('date_cloture')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};