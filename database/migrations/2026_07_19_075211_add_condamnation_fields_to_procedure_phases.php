<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_phases', function (Blueprint $table) {
            // Ajouter les champs de condamnation
            $table->boolean('est_condamne')->default(false)->after('description');
            $table->string('peine_principale')->nullable()->after('est_condamne');
            $table->text('peine_description')->nullable()->after('peine_principale');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_phases', function (Blueprint $table) {
            $table->dropColumn(['est_condamne', 'peine_principale', 'peine_description']);
        });
    }
};