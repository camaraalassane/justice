<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('phase_types')) {
            Schema::create('phase_types', function (Blueprint $table) {
                $table->id();
                $table->string('libelle');
                $table->string('slug')->unique();
                $table->boolean('is_system')->default(false);
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('procedure_phases')) {
            Schema::create('procedure_phases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
                $table->foreignId('phase_type_id')->nullable()->constrained('phase_types')->nullOnDelete();
                $table->string('libelle_personnalise')->nullable();
                $table->integer('ordre')->default(0);
                $table->date('date_phase')->nullable();
                $table->text('description')->nullable();
                $table->boolean('est_retour')->default(false);
                $table->foreignId('phase_precedente_id')->nullable()->constrained('procedure_phases')->nullOnDelete();
                $table->foreignId('cree_par')->constrained('users');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_champs')) {
            Schema::create('phase_champs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('cle');
                $table->text('valeur')->nullable();
                $table->string('type')->default('text');
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_personnes')) {
            Schema::create('phase_personnes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('nom');
                $table->string('prenom');
                $table->string('profession')->nullable();
                $table->string('autre')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_evenements')) {
            Schema::create('phase_evenements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('nom');
                $table->date('date_evenement')->nullable();
                $table->text('description')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_references')) {
            Schema::create('phase_references', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('libelle');
                $table->text('description')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_pieces_jointes')) {
            Schema::create('phase_pieces_jointes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->string('chemin_fichier')->nullable();
                $table->string('contexte')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('phase_options_cocher')) {
            Schema::create('phase_options_cocher', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procedure_phase_id')->constrained('procedure_phases')->cascadeOnDelete();
                $table->string('libelle');
                $table->boolean('est_coche')->default(false);
                $table->text('description')->nullable();
                $table->integer('ordre')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('phase_options_cocher');
        Schema::dropIfExists('phase_pieces_jointes');
        Schema::dropIfExists('phase_references');
        Schema::dropIfExists('phase_evenements');
        Schema::dropIfExists('phase_personnes');
        Schema::dropIfExists('phase_champs');
        Schema::dropIfExists('procedure_phases');
        Schema::dropIfExists('phase_types');
    }
};