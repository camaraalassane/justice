<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhaseType extends Model
{
    protected $fillable = ['libelle', 'slug', 'is_system', 'ordre'];

    protected $casts = ['is_system' => 'boolean'];

    public function procedurePhases()
    {
        return $this->hasMany(ProcedurePhase::class);
    }
}