<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne table
        Schema::dropIfExists('fautes_militaires');
        
        // Recréer la table avec les nouvelles colonnes
        Schema::create('fautes_militaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categorie_faute_id')->constrained('categorie_fautes')->onDelete('cascade');
            $table->string('libelle');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['categorie_faute_id', 'libelle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fautes_militaires');
        // Recréer l'ancienne table si nécessaire
    }
};