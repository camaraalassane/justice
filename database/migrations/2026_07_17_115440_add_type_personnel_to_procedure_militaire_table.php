<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            if (!Schema::hasColumn('procedure_militaire', 'type_personnel')) {
                $table->enum('type_personnel', ['militaire', 'civil'])->default('militaire')->after('procedure_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedure_militaire', function (Blueprint $table) {
            $table->dropColumn('type_personnel');
        });
    }
};