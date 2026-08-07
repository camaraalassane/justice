<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armees', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Ajouter la colonne armee_id dans militaires
        Schema::table('militaires', function (Blueprint $table) {
            if (!Schema::hasColumn('militaires', 'armee_id')) {
                $table->foreignId('armee_id')->nullable()->constrained('armees')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropForeign(['armee_id']);
            $table->dropColumn('armee_id');
        });
        Schema::dropIfExists('armees');
    }
};