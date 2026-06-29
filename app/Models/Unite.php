<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unite extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_unite',
        'nom_unite',
        'type_unite',
        'unite_parent_id',
        'localisation',
    ];

    // Une unité peut être rattachée à une unité parente
    public function uniteParent()
    {
        return $this->belongsTo(Unite::class, 'unite_parent_id');
    }

    // Une unité peut avoir des sous-unités
    public function sousUnites()
    {
        return $this->hasMany(Unite::class, 'unite_parent_id');
    }

    // Militaires appartenant à cette unité
    public function militaires()
    {
        return $this->hasMany(Militaire::class);
    }
}