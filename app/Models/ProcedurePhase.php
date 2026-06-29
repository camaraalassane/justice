<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedurePhase extends Model
{
    protected $fillable = [
        'procedure_id',
        'phase_type_id',
        'libelle_personnalisee',
        'ordre',
        'date_phase',
        'description',
        'est_retour',
        'phase_precedente_id',
        'cree_par'
    ];

    protected $casts = [
        'date_phase' => 'date',
        'est_retour' => 'boolean',
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
        return $this->hasMany(PhaseChamp::class)->orderBy('ordre');
    }

    public function personnes()
    {
        return $this->hasMany(PhasePersonne::class)->orderBy('ordre');
    }

    public function evenements()
    {
        return $this->hasMany(PhaseEvenement::class)->orderBy('ordre');
    }

    public function references()
    {
        return $this->hasMany(PhaseReference::class)->orderBy('ordre');
    }

    public function piecesJointes()
    {
        return $this->hasMany(PhasePieceJointe::class)->orderBy('ordre');
    }

    public function optionsCocher()
    {
        return $this->hasMany(PhaseOptionCocher::class)->orderBy('ordre');
    }

    public function getLibelleAttribute()
    {
        return $this->libelle_personnalisee ?? $this->phaseType?->libelle ?? 'Phase sans nom';
    }
}