<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_infraction', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained('procedures')->cascadeOnDelete();
            $table->foreignId('infraction_base_id')->constrained('infractions_base');
            $table->string('qualification')->nullable(); // Ex: "Tentative", "Complicité"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_infraction');
    }
};