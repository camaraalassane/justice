<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jugement extends Model
{
    protected $table = 'jugements';

    protected $fillable = [
        'procedure_id',
        'date_jugement',
        'juridiction',
        'numero_jugement',
        'verdict',
        'peine_principale',
        'peines_complementaires',
        'duree_peine_jours',
        'est_definitif',
        'date_appel',
    ];

    protected $casts = [
        'date_jugement' => 'date',
        'date_appel' => 'date',
        'est_definitif' => 'boolean',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}