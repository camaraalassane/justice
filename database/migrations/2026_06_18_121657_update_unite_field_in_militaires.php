<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            // Ajouter la colonne unite (string)
            if (!Schema::hasColumn('militaires', 'unite')) {
                $table->string('unite')->nullable()->after('grade_id');
            }

            // Supprimer la contrainte étrangère et la colonne unite_id si elle existe
            if (Schema::hasColumn('militaires', 'unite_id')) {
                $table->dropForeign(['unite_id']);
                $table->dropColumn('unite_id');
            }

            // Rendre grade nullable (au cas où)
            $table->string('grade')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('militaires', function (Blueprint $table) {
            $table->dropColumn('unite');
            $table->foreignId('unite_id')->nullable()->constrained('unites')->nullOnDelete();
        });
    }
};