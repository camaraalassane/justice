<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->date('date_naissance')->nullable()->change();
            $table->string('matricule')->nullable()->change();
            $table->foreignId('grade_id')->nullable()->change();
            $table->string('unite')->nullable()->change();
            $table->string('genre')->nullable()->change();
            $table->string('armee')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->date('date_naissance')->nullable(false)->change();
            $table->string('matricule')->nullable(false)->change();
            $table->foreignId('grade_id')->nullable(false)->change();
            $table->string('unite')->nullable(false)->change();
            $table->string('genre')->nullable(false)->change();
            $table->string('armee')->nullable(false)->change();
        });
    }
};