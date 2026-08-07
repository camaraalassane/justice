<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieFaute extends Model
{
    use SoftDeletes;

    protected $table = 'categorie_fautes';

    protected $fillable = [
        'libelle',
        'description',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fautes()
    {
        return $this->hasMany(FauteMilitaire::class, 'categorie_faute_id')->orderBy('ordre');
    }

    public function fautesActives()
    {
        return $this->hasMany(FauteMilitaire::class, 'categorie_faute_id')
            ->where('is_active', true)
            ->orderBy('ordre');
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParOrdre($query)
    {
        return $query->orderBy('ordre')->orderBy('libelle');
    }
}