<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriquePhase extends Model
{
    protected $table = 'historique_phases';

    protected $fillable = [
        'procedure_id',
        'phase_avant',
        'phase_apres',
        'type_document',
        'numero_document',
        'date_document',
        'commentaire',
        'utilisateur_id',
    ];

    protected $casts = [
        'date_document' => 'date',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}