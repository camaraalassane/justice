<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parties_civiles', function (Blueprint $table) {
            $table->enum('type', ['Personne', 'Structure'])->default('Personne')->after('id');
            $table->string('nom')->nullable()->change();
            $table->string('prenom')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('parties_civiles', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('nom')->nullable(false)->change();
            $table->string('prenom')->nullable(false)->change();
        });
    }
};