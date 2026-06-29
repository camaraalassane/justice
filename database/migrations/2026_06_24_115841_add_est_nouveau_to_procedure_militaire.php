<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            if (!Schema::hasColumn('procedure_militaire', 'est_nouveau')) {
                $table->boolean('est_nouveau')->default(false)->after('champs_personnalises');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            if (Schema::hasColumn('procedure_militaire', 'est_nouveau')) {
                $table->dropColumn('est_nouveau');
            }
        });
    }
};