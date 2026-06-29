<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcedureMilitaire extends Model
{
    protected $table = 'procedure_militaire';

    protected $fillable = [
        'procedure_id',
        'militaire_id',
        'infractions',
        'fautes_militaires',
        'parties_civiles',
        'champs_personnalises',
        'est_nouveau',
        'nom_temp',
        'prenom_temp',
        'grade_temp',
        'matricule_temp',
    ];

    protected $casts = [
        'infractions' => 'array',
        'fautes_militaires' => 'array',
        'parties_civiles' => 'array',
        'champs_personnalises' => 'array',
        'est_nouveau' => 'boolean',
    ];

    // ==================== RELATIONS ====================

    public function procedure()
    {
        return $this->belongsTo(Procedure::class);
    }

    public function militaire()
    {
        return $this->belongsTo(Militaire::class);
    }

    // ==================== ATTRIBUTS ====================

    public function getNomCompletAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->nom . ' ' . $this->militaire->prenoms;
        }
        return ($this->nom_temp ?? '') . ' ' . ($this->prenom_temp ?? '');
    }

    public function getNomAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->nom;
        }
        return $this->nom_temp;
    }

    public function getPrenomAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->prenoms;
        }
        return $this->prenom_temp;
    }

    public function getGradeAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->grade;
        }
        return $this->grade_temp;
    }

    public function getMatriculeAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->matricule;
        }
        return $this->matricule_temp;
    }

    public function getUniteAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->unite;
        }
        return null;
    }

    public function getStatutAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->statut;
        }
        return 'Actif';
    }

    public function getGenreAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->genre;
        }
        return null;
    }

    public function getArmeeAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->armee;
        }
        return null;
    }

    public function getDateNaissanceAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->date_naissance;
        }
        return null;
    }

    public function getAdresseAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->adresse;
        }
        return null;
    }

    public function getTelephoneAttribute()
    {
        if ($this->militaire) {
            return $this->militaire->telephone;
        }
        return null;
    }

    /**
     * Vérifier si le militaire existe en base
     */
    public function getEstExistantAttribute(): bool
    {
        return !is_null($this->militaire_id);
    }

    // ==================== MÉTHODES POUR LES INFRACTIONS ====================

    /**
     * Récupérer les infractions avec leurs libellés
     */
    public function getInfractionsLibellesAttribute()
    {
        if (empty($this->infractions)) {
            return collect();
        }
        return InfractionBase::whereIn('id', $this->infractions)->get();
    }

    /**
     * Récupérer les infractions avec leurs codes
     */
    public function getInfractionsDetailsAttribute()
    {
        if (empty($this->infractions)) {
            return collect();
        }
        return InfractionBase::whereIn('id', $this->infractions)
            ->select('id', 'code_infraction', 'libelle', 'classification')
            ->get();
    }

    /**
     * Vérifier si le militaire a des infractions
     */
    public function hasInfractions(): bool
    {
        return !empty($this->infractions) && count($this->infractions) > 0;
    }

    /**
     * Récupérer les fautes militaires
     */
    public function getFautesLibellesAttribute()
    {
        if (empty($this->fautes_militaires)) {
            return collect();
        }
        return collect($this->fautes_militaires);
    }

    /**
     * Récupérer les parties civiles
     */
    public function getPartiesCivilesDetailsAttribute()
    {
        if (empty($this->parties_civiles)) {
            return collect();
        }
        return collect($this->parties_civiles);
    }

    /**
     * Vérifier si le militaire a des parties civiles
     */
    public function hasPartiesCiviles(): bool
    {
        return !empty($this->parties_civiles) && count($this->parties_civiles) > 0;
    }

    /**
     * Vérifier si le militaire a des fautes
     */
    public function hasFautes(): bool
    {
        return !empty($this->fautes_militaires) && count($this->fautes_militaires) > 0;
    }
}