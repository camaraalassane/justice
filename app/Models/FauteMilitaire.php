<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FauteMilitaire extends Model
{
    use SoftDeletes;

    protected $table = 'fautes_militaires';

    protected $fillable = [
        'categorie_faute_id',
        'libelle',
        'code',
        'description',
        'ordre',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categorie()
    {
        return $this->belongsTo(CategorieFaute::class, 'categorie_faute_id');
    }

    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeParOrdre($query)
    {
        return $query->orderBy('ordre')->orderBy('libelle');
    }

    public function getCategorieLibelleAttribute()
    {
        return $this->categorie?->libelle ?? 'Non catégorisé';
    }
}