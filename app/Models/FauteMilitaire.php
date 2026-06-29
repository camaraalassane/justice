<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FauteMilitaire extends Model
{
    protected $table = 'fautes_militaires';

    protected $fillable = ['procedure_id', 'libelle', 'description', 'ordre'];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }
}