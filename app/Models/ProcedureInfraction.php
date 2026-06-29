<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureInfraction extends Model
{
    protected $table = 'procedure_infraction';

    protected $fillable = [
        'procedure_id',
        'infraction_base_id',
        'qualification',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function infraction()
    {
        return $this->belongsTo(InfractionBase::class, 'infraction_base_id');
    }
}