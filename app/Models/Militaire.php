<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Militaire extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'matricule',
        'nom',
        'prenoms',
        'date_naissance',
        'grade',
        'grade_id',
        'unite',
        'adresse',
        'telephone',
        'statut',
        'genre',
        'armee',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    protected $attributes = [
        'statut' => 'Actif',
    ];

    // ==================== RELATIONS ====================

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // Relation avec les procédures via la table pivot (pour la pluralité)
    public function procedureMilitaires()
    {
        return $this->hasMany(ProcedureMilitaire::class);
    }

    // Récupérer toutes les procédures (directes + via pivot)
    public function toutesProcedures()
    {
        $principal = $this->procedures()->get()->map(function($p) {
            $p->est_principal = true;
            return $p;
        });
        
        $viaPivot = $this->procedureMilitaires()
            ->with('procedure')
            ->get()
            ->pluck('procedure')
            ->map(function($p) {
                $p->est_principal = false;
                return $p;
            });
        
        return $principal->merge($viaPivot)->unique('id');
    }

    // ==================== SCOPES ====================

    public function scopeRecherche($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $termes = explode(' ', trim($search));

        return $query->where(function ($q) use ($termes) {
            foreach ($termes as $terme) {
                $terme = trim($terme);
                if (strlen($terme) >= 2) {
                    $q->where(function ($subQ) use ($terme) {
                        $subQ->where('nom', 'ILIKE', "%{$terme}%")
                             ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                             ->orWhere('matricule', 'ILIKE', "%{$terme}%")
                             ->orWhere('unite', 'ILIKE', "%{$terme}%");
                    });
                }
            }
        });
    }
}