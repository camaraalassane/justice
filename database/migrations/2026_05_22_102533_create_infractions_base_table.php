<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infractions_base', function (Blueprint $table) {
            $table->id();
            $table->string('code_infraction', 20)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->string('classification');
            $table->string('nature');
            $table->integer('gravite')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infractions_base');
    }
};