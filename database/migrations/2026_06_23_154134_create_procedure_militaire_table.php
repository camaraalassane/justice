<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_militaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained()->onDelete('cascade');
            $table->foreignId('militaire_id')->constrained()->onDelete('cascade');
            $table->json('infractions')->nullable();
            $table->json('fautes_militaires')->nullable();
            $table->json('parties_civiles')->nullable();
            $table->json('champs_personnalises')->nullable();
            $table->boolean('est_nouveau')->default(false);
            $table->string('nom_temp')->nullable();
            $table->string('prenom_temp')->nullable();
            $table->string('grade_temp')->nullable();
            $table->string('matricule_temp')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_militaire');
    }
};