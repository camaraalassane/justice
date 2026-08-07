<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->enum('parquet_type', ['militaire', 'droit_commun'])->default('militaire')->after('parquet_competent');
            $table->foreignId('parquet_id')->nullable()->after('parquet_type')->constrained()->onDelete('set null');
            
            // Renommer l'ancienne colonne pour garder l'historique
            $table->renameColumn('parquet_competent', 'parquet_competent_old');
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn(['parquet_type', 'parquet_id']);
            $table->renameColumn('parquet_competent_old', 'parquet_competent');
        });
    }
};