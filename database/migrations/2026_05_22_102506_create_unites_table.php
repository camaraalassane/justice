<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unites', function (Blueprint $table) {
            $table->id();
            $table->string('code_unite', 50)->unique();
            $table->string('nom_unite');
            $table->enum('type_unite', ['Compagnie', 'Bataillon', 'Régiment', 'Brigade', 'Division', 'État-Major']);
            $table->foreignId('unite_parent_id')->nullable()->constrained('unites')->nullOnDelete();
            $table->string('localisation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unites');
    }
};