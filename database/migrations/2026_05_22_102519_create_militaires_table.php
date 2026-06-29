<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('militaires', function (Blueprint $table) {
            $table->id();
            $table->string('matricule', 50)->unique();
            $table->string('nom');
            $table->string('prenoms');
            $table->date('date_naissance');
            $table->string('grade');
            $table->foreignId('unite_id')->constrained('unites');
            $table->text('adresse')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->enum('statut', ['Actif', 'Suspendu', 'Déserteur', 'Radié'])->default('Actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('militaires');
    }
};