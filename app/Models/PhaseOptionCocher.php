<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseOptionCocher extends Model
{
    protected $table = 'phase_options_cocher';

    protected $fillable = ['procedure_phase_id', 'libelle', 'est_coche', 'description', 'ordre'];

    protected $casts = ['est_coche' => 'boolean'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}