<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_phases', function (Blueprint $table) {
            // Ajouter la colonne deleted_at pour SoftDeletes
            if (!Schema::hasColumn('procedure_phases', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_phases', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};