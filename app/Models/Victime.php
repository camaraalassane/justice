<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Victime extends Model
{
    protected $fillable = [
        'procedure_id',
        'nom',
        'prenom',
        'profession',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}