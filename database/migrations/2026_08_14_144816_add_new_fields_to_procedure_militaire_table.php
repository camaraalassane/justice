<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            $table->json('temoins')->nullable()->after('parties_civiles');
            $table->json('civile_responsables')->nullable()->after('temoins');
            $table->json('garants')->nullable()->after('civile_responsables');
            $table->json('avocats')->nullable()->after('garants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            $table->dropColumn(['temoins', 'civile_responsables', 'garants', 'avocats']);
        });
    }
};
