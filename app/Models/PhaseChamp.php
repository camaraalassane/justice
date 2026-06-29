<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseChamp extends Model
{
    protected $fillable = ['procedure_phase_id', 'cle', 'valeur', 'type', 'ordre'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}