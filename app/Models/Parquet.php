<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parquet extends Model
{
    protected $fillable = [
        'nom',
        'type',
        'localisation',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope pour les parquets militaires
    public function scopeMilitaire($query)
    {
        return $query->where('type', 'militaire');
    }

    // Scope pour les parquets de droit commun
    public function scopeDroitCommun($query)
    {
        return $query->where('type', 'droit_commun');
    }

    // Scope pour les parquets actifs
    public function scopeActif($query)
    {
        return $query->where('is_active', true);
    }
}