<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            if (!Schema::hasColumn('procedures', 'lieu_commission')) {
                $table->enum('lieu_commission', ['Organique', 'Operation'])->nullable()->after('est_plurielle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropColumn('lieu_commission');
        });
    }
};