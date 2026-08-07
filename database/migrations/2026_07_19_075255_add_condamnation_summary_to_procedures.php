<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Résumé de la condamnation dans la procédure
            $table->boolean('est_condamne')->default(false)->after('phase');
            $table->string('peine_principale')->nullable()->after('est_condamne');
            $table->json('condamnation_details')->nullable()->after('peine_principale');
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn(['est_condamne', 'peine_principale', 'condamnation_details']);
        });
    }
};