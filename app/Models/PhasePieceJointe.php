<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhasePieceJointe extends Model
{
    protected $table = 'phase_pieces_jointes';

    protected $fillable = ['procedure_phase_id', 'nom', 'description', 'chemin_fichier', 'contexte', 'ordre'];

    public function procedurePhase()
    {
        return $this->belongsTo(ProcedurePhase::class);
    }
}