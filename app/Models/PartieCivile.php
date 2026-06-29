<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartieCivile extends Model
{
    protected $table = 'parties_civiles';

    protected $fillable = [
        'procedure_id',
        'type',
        'nom',
        'prenom',
        'profession',
        'adresse',
    ];

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function getLibelleCompletAttribute(): string
    {
        if ($this->type === 'Structure') {
            return $this->nom ?? 'Structure sans nom';
        }
        return trim("{$this->nom} {$this->prenom}");
    }

    public function getIsPersonneAttribute(): bool
    {
        return $this->type === 'Personne';
    }

    public function getIsStructureAttribute(): bool
    {
        return $this->type === 'Structure';
    }
}