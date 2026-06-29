<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Supprimer les colonnes obsolètes
            $table->dropColumn([
                'reference_doe',
                'motif_doe',
                'statut_jugement',
                'date_ordre_poursuite',
                'date_mise_disposition',
                'unite_mad_id',
                'date_notification',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            $table->string('reference_doe')->nullable();
            $table->text('motif_doe')->nullable();
            $table->string('statut_jugement')->default('En_attente');
            $table->timestamp('date_ordre_poursuite')->nullable();
            $table->timestamp('date_mise_disposition')->nullable();
            $table->foreignId('unite_mad_id')->nullable()->constrained('unites');
            $table->timestamp('date_notification')->nullable();
        });
    }
};