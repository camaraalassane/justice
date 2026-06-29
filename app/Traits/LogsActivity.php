<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public function logActivity($action, $modelType = null, $modelId = null, $description = '', $details = null)
    {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $this->cleanString($description),
            'details' => $details,
            'ip_address' => request()->ip(),
        ]);
    }

    public function logCreate($model, $description = null)
    {
        $modelName = class_basename($model);
        return $this->logActivity(
            'create',
            $modelName,
            $model->id,
            $description ?? "Création : {$modelName} #{$model->id}",
            ['data' => $model->toArray()]
        );
    }

    public function logUpdate($model, $description = null, $changes = null)
    {
        $modelName = class_basename($model);
        return $this->logActivity(
            'update',
            $modelName,
            $model->id,
            $description ?? "Modification : {$modelName} #{$model->id}",
            ['changes' => $changes ?: $model->getChanges()]
        );
    }

    public function logDelete($model, $description = null)
    {
        $modelName = class_basename($model);
        return $this->logActivity(
            'delete',
            $modelName,
            $model->id,
            $description ?? "Suppression : {$modelName} #{$model->id}",
            ['data' => $model->toArray()]
        );
    }

public function logPhaseChange($procedure, $phaseAvant, $phaseApres, $commentaire = null)
{
    return $this->logActivity(
        'phase_change',
        'Procedure',
        $procedure->id,
        "Phase : " . str_replace('_', ' ', $phaseAvant) . " -> " . str_replace('_', ' ', $phaseApres),
        [
            'phase_avant' => $phaseAvant,
            'phase_apres' => $phaseApres,
            'commentaire' => $commentaire,
        ]
    );
}

    public function logLogin()
    {
        return $this->logActivity(
            'login',
            null,
            null,
            'Connexion à l\'application'
        );
    }

    public function logLogout()
    {
        return $this->logActivity(
            'logout',
            null,
            null,
            'Déconnexion de l\'application'
        );
    }

    /**
     * Nettoie une chaîne des caractères problématiques pour l'encodage
     */
    private function cleanString($string): string
    {
        if (is_null($string)) {
            return '';
        }

        // Remplacer les caractères Unicode problématiques
        $replacements = [
            '→' => '->',
            '←' => '<-',
            '↑' => '^',
            '↓' => 'v',
            '⇒' => '=>',
            '⇐' => '<=',
            '–' => '-',
            '—' => '-',
            '…' => '...',
            '«' => '"',
            '»' => '"',
            '\'' => "'",
            '\'' => "'",
            '"' => '"',
            '"' => '"',
            'é' => 'é',
            'è' => 'è',
            'ê' => 'ê',
            'ë' => 'ë',
            'à' => 'à',
            'â' => 'â',
            'ä' => 'ä',
            'ù' => 'ù',
            'û' => 'û',
            'ü' => 'ü',
            'ô' => 'ô',
            'ö' => 'ö',
            'î' => 'î',
            'ï' => 'ï',
            'ç' => 'ç',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }
}