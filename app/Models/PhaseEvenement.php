<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseEvenement extends Model
{
    protected $fillable = ['procedure_phase_id', 'nom', 'date_evenement', 'description', 'ordre'];

    protected $casts = ['date_evenement' => 'date'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}