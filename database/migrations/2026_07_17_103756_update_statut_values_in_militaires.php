<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ÉTAPE 1 : Supprimer l'ancienne contrainte
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE militaires DROP CONSTRAINT IF EXISTS militaires_statut_check');
        }
        
        // ÉTAPE 2 : Mettre à jour toutes les données existantes
        // Convertir les anciens statuts vers les nouveaux
        DB::table('militaires')
            ->where('statut', 'Actif')
            ->update(['statut' => 'En activité']);
        
        DB::table('militaires')
            ->whereIn('statut', ['Suspendu', 'Déserteur', 'Non en activité'])
            ->update(['statut' => 'Non activite']);
        
        // ÉTAPE 3 : Ajouter la nouvelle contrainte avec les bons statuts
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE militaires ADD CONSTRAINT militaires_statut_check CHECK (statut IN ('En activité', 'Non activite', 'En retraite', 'Radié'))");
        }
    }

    public function down(): void
    {
        // Supprimer la contrainte
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE militaires DROP CONSTRAINT IF EXISTS militaires_statut_check');
        }
        
        // Remettre les anciennes valeurs
        DB::table('militaires')
            ->where('statut', 'En activité')
            ->update(['statut' => 'Actif']);
        
        DB::table('militaires')
            ->where('statut', 'Non activite')
            ->update(['statut' => 'Suspendu']);
        
        // Recréer l'ancienne contrainte
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE militaires ADD CONSTRAINT militaires_statut_check CHECK (statut IN ('Actif', 'Suspendu', 'Déserteur', 'Radié'))");
        }
    }
};