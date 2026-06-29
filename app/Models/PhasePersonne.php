<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhasePersonne extends Model
{
    protected $fillable = ['procedure_phase_id', 'nom', 'prenom', 'profession', 'autre', 'ordre'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}