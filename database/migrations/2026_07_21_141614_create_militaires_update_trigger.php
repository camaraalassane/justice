<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'pgsql') {
            // Supprimer le trigger s'il existe déjà
            DB::statement("DROP TRIGGER IF EXISTS update_procedure_militaire_trigger ON militaires");
            DB::statement("DROP FUNCTION IF EXISTS update_procedure_militaire()");

            // Créer la fonction
            DB::statement("
                CREATE OR REPLACE FUNCTION update_procedure_militaire()
                RETURNS TRIGGER AS $$
                BEGIN
                    -- Mettre à jour procedure_militaire pour les militaires existants
                    UPDATE procedure_militaire
                    SET 
                        type_personnel = NEW.type_personnel,
                        nom_temp = CASE 
                            WHEN est_nouveau = true THEN NEW.nom 
                            ELSE nom_temp 
                        END,
                        prenom_temp = CASE 
                            WHEN est_nouveau = true THEN NEW.prenoms 
                            ELSE prenom_temp 
                        END,
                        grade_temp = CASE 
                            WHEN est_nouveau = true THEN NEW.grade 
                            ELSE grade_temp 
                        END,
                        matricule_temp = CASE 
                            WHEN est_nouveau = true THEN NEW.matricule 
                            ELSE matricule_temp 
                        END,
                        profession_temp = CASE 
                            WHEN est_nouveau = true THEN NEW.profession 
                            ELSE profession_temp 
                        END
                    WHERE militaire_id = NEW.id;
                    
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // Créer le trigger
            DB::statement("
                CREATE TRIGGER update_procedure_militaire_trigger
                AFTER UPDATE ON militaires
                FOR EACH ROW
                EXECUTE FUNCTION update_procedure_militaire();
            ");
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("DROP TRIGGER IF EXISTS update_procedure_militaire_trigger ON militaires");
            DB::statement("DROP FUNCTION IF EXISTS update_procedure_militaire()");
        }
    }
};