<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories_grades', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('abreviation');
            $table->foreignId('categorie_grade_id')->constrained('categories_grades')->cascadeOnDelete();
            $table->integer('ordre')->default(0);
            $table->integer('age_limite')->nullable();
            $table->timestamps();
        });

        // Ajouter les colonnes à la table militaires
        Schema::table('militaires', function (Blueprint $table) {
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->enum('genre', ['Masculin', 'Féminin'])->nullable();
            $table->string('armee')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropColumn('grade_id');
            $table->dropColumn('genre');
            $table->dropColumn('armee');
        });
        Schema::dropIfExists('grades');
        Schema::dropIfExists('categories_grades');
    }
};