<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedurePhase extends Model
{
    use SoftDeletes;

    protected $table = 'procedure_phases';

    protected $fillable = [
        'procedure_id',
        'phase_type_id',
        'libelle_personnalisee',
        'date_phase',
        'description',
        'est_condamne',        // NOUVEAU
        'peine_principale',    // NOUVEAU
        'peine_description',   // NOUVEAU
        'ordre',
        'est_retour',
        'phase_precedente_id',
        'cree_par',
        'champs',
        'personnes',
        'evenements',
        'references',
        'options_cocher',
        'pieces_jointes',
    ];

    protected $casts = [
        'date_phase' => 'datetime',
        'est_retour' => 'boolean',
        'est_condamne' => 'boolean',  // NOUVEAU
        'champs' => 'array',
        'personnes' => 'array',
        'evenements' => 'array',
        'references' => 'array',
        'options_cocher' => 'array',
        'pieces_jointes' => 'array',
        'condamnation_details' => 'array',  // NOUVEAU
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function phaseType()
    {
        return $this->belongsTo(PhaseType::class);
    }

    public function phasePrecedente()
    {
        return $this->belongsTo(ProcedurePhase::class, 'phase_precedente_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function champs()
    {
        return $this->hasMany(PhaseChamp::class);
    }

    public function personnes()
    {
        return $this->hasMany(PhasePersonne::class);
    }

    public function evenements()
    {
        return $this->hasMany(PhaseEvenement::class);
    }

    public function references()
    {
        return $this->hasMany(PhaseReference::class);
    }

    public function optionsCocher()
    {
        return $this->hasMany(PhaseOptionCocher::class);
    }

    public function piecesJointes()
    {
        return $this->hasMany(PhasePieceJointe::class);
    }

    public function getLibelleAttribute()
    {
        return $this->libelle_personnalisee ?? $this->phaseType?->libelle ?? 'Phase';
    }

    // ====== CONDAMNATION ======
    public function getEstCondamneAttribute($value)
    {
        return (bool) $value;
    }

    public function getPeineAfficheeAttribute()
    {
        if (!$this->est_condamne) {
            return null;
        }
        return $this->peine_principale ?? 'Peine non spécifiée';
    }

    public function getCondamnationDetailsAttribute()
    {
        return [
            'est_condamne' => $this->est_condamne,
            'peine_principale' => $this->peine_principale,
            'peine_description' => $this->peine_description,
            'date_condamnation' => $this->date_phase,
        ];
    }
}