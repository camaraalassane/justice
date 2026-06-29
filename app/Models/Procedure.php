<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero_procedure',
        'militaire_id',
        'phase',
        'est_plurielle',
        'date_ouverture',
        'date_cloture',
        'parquet_competent',
        'cree_par',
        'valide_par',
    ];

    protected $casts = [
        'date_ouverture' => 'datetime',
        'date_cloture' => 'datetime',
        'est_plurielle' => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function militaire()
    {
        return $this->belongsTo(Militaire::class);
    }

    public function procedureMilitaires()
    {
        return $this->hasMany(ProcedureMilitaire::class);
    }

    public function militaires()
    {
        return $this->belongsToMany(Militaire::class, 'procedure_militaire')
                    ->withPivot('infractions', 'fautes_militaires', 'parties_civiles', 'champs_personnalises', 'est_nouveau')
                    ->withTimestamps();
    }

    // ==================== RELATION PROCEDURE PHASES ====================
    
    public function procedurePhases()
    {
        return $this->hasMany(ProcedurePhase::class);
    }

    public function phaseActuelle()
    {
        return $this->hasOne(ProcedurePhase::class)->latestOfMany();
    }

    // ==================== RELATIONS EXISTANTES ====================

    public function infractions()
    {
        return $this->belongsToMany(InfractionBase::class, 'procedure_infraction')
                    ->withPivot('qualification')
                    ->withTimestamps();
    }

    public function fautesMilitaires()
    {
        return $this->hasMany(FauteMilitaire::class)->orderBy('ordre');
    }

    public function documents()
    {
        return $this->hasMany(DocumentProcedure::class);
    }

    public function jugement()
    {
        return $this->hasOne(Jugement::class);
    }

    public function partiesCiviles()
    {
        return $this->hasMany(PartieCivile::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // ==================== SCOPES ====================

    public function scopeEnCours($query)
    {
        return $query->where('phase', '!=', 'Cloturee');
    }

    public function scopeParPhase($query, $phase)
    {
        return $query->where('phase', $phase);
    }

    public function scopePlurielle($query)
    {
        return $query->where('est_plurielle', true);
    }

    public function scopeIndividuelle($query)
    {
        return $query->where('est_plurielle', false);
    }

    public function scopeEntreDates($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_ouverture', [$dateDebut, $dateFin]);
    }

    // ==================== MÉTHODES ====================

    public static function genererNumero(): string
    {
        $annee = now()->year;
        $dernier = self::withTrashed()
            ->whereYear('created_at', $annee)
            ->orderBy('id', 'desc')
            ->first();

        $increment = 1;
        if ($dernier && $dernier->numero_procedure) {
            $parts = explode('-', $dernier->numero_procedure);
            $lastNumber = intval(end($parts));
            $increment = $lastNumber + 1;
        }

        return 'GPJ-' . $annee . '-' . str_pad($increment, 4, '0', STR_PAD_LEFT);
    }
}