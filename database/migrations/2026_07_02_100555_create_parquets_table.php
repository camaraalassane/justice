<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parquets', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->enum('type', ['militaire', 'droit_commun']);
            $table->string('localisation')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parquets');
    }
};