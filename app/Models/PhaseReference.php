<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseReference extends Model
{
    protected $fillable = ['procedure_phase_id', 'libelle', 'description', 'ordre'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}