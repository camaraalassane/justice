<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procedure extends Model
{
    use SoftDeletes;

    protected $table = 'procedures';

    protected $fillable = [
        'numero_procedure',
        'militaire_id',
        'phase',
        'est_plurielle',
        'lieu_commission',
        'date_ouverture',
        'date_cloture',
        'parquet_type',
        'parquet_id',
        'cree_par',
        'valide_par',
        'est_condamne',        // NOUVEAU
        'peine_principale',    // NOUVEAU
        'condamnation_details', // NOUVEAU
    ];

    protected $casts = [
        'date_ouverture' => 'datetime',
        'date_cloture' => 'datetime',
        'est_plurielle' => 'boolean',
        'est_condamne' => 'boolean',  // NOUVEAU
        'condamnation_details' => 'array',  // NOUVEAU
    ];

    // ==================== RELATIONS ====================

    public function militaire()
    {
        return $this->belongsTo(Militaire::class);
    }

    public function parquet()
    {
        return $this->belongsTo(Parquet::class);
    }

    public function procedureMilitaires()
    {
        return $this->hasMany(ProcedureMilitaire::class);
    }

    public function militaires()
    {
        return $this->belongsToMany(Militaire::class, 'procedure_militaire')
                    ->withPivot('type_personnel', 'infractions', 'fautes_militaires', 'parties_civiles', 'champs_personnalises', 'est_nouveau')
                    ->withTimestamps();
    }

    public function procedurePhases()
    {
        return $this->hasMany(ProcedurePhase::class);
    }

    public function phaseActuelle()
    {
        return $this->hasOne(ProcedurePhase::class)->latestOfMany();
    }

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

    // ==================== ATTRIBUTS ====================

    public function getParquetNomAttribute()
    {
        if ($this->parquet) {
            return $this->parquet->nom;
        }
        return 'Non défini';
    }

    public function getParquetTypeLabelAttribute()
    {
        return $this->parquet_type === 'militaire' ? 'Militaire' : 'Droit Commun';
    }

    public function getParquetCompetentAttribute()
    {
        return $this->parquet_nom;
    }

    public function getLieuCommissionLabelAttribute()
    {
        return $this->lieu_commission ?? 'Non défini';
    }

    // ====== CONDAMNATION ======
    public function getCondamnationSummaryAttribute()
    {
        if (!$this->est_condamne) {
            return null;
        }
        return [
            'est_condamne' => $this->est_condamne,
            'peine_principale' => $this->peine_principale,
            'date' => $this->date_ouverture,
        ];
    }

    public function scopeCondamnes($query)
    {
        return $query->where('est_condamne', true);
    }

    public function scopeNonCondamnes($query)
    {
        return $query->where('est_condamne', false);
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

    public function scopeParMois($query, $mois, $annee)
    {
        return $query->whereMonth('date_ouverture', $mois)
                     ->whereYear('date_ouverture', $annee);
    }

    public function scopeParAnnee($query, $annee)
    {
        return $query->whereYear('date_ouverture', $annee);
    }

    public function scopeParJour($query, $date)
    {
        return $query->whereDate('date_ouverture', $date);
    }

    public function scopeParTypePersonnel($query, $type)
    {
        return $query->whereHas('procedureMilitaires', function($q) use ($type) {
            $q->where('type_personnel', $type);
        });
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