<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Militaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'militaires';

    protected $fillable = [
        'type_personnel',
        'matricule',
        'nom',
        'prenoms',
        'profession',
        'date_naissance',
        'lieu_naissance',
        'nom_pere',
        'prenoms_pere',
        'nom_mere',
        'prenoms_mere',
        'grade_id',
        'unite',
        'adresse',
        'telephone',
        'statut',
        'genre',
        'armee_id',
        'armee',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // ==================== RELATIONS ====================

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function armeeRelation()
    {
        return $this->belongsTo(Armee::class, 'armee_id');
    }

    public function procedures()
    {
        return $this->belongsToMany(Procedure::class, 'procedure_militaire')
                    ->withPivot('type_personnel', 'infractions', 'fautes_militaires', 'parties_civiles', 'champs_personnalises', 'est_nouveau')
                    ->withTimestamps();
    }

    public function procedureMilitaires()
    {
        return $this->hasMany(ProcedureMilitaire::class);
    }

    // ==================== SCOPES ====================

    public function scopeRecherche($query, $search)
    {
        if (empty($search)) {
            return $query;
        }

        $termes = explode(' ', trim($search));
        return $query->where(function($q) use ($termes) {
            foreach ($termes as $terme) {
                $terme = trim($terme);
                if (strlen($terme) >= 2) {
                    $q->where('nom', 'ILIKE', "%{$terme}%")
                      ->orWhere('prenoms', 'ILIKE', "%{$terme}%")
                      ->orWhere('matricule', 'ILIKE', "%{$terme}%")
                      ->orWhere('profession', 'ILIKE', "%{$terme}%")
                      ->orWhere('unite', 'ILIKE', "%{$terme}%");
                }
            }
        });
    }

    public function scopeDeType($query, $type)
    {
        if ($type) {
            return $query->where('type_personnel', $type);
        }
        return $query;
    }

    public function scopeAvecStatut($query, $statut)
    {
        if ($statut) {
            return $query->where('statut', $statut);
        }
        return $query;
    }

    // ==================== ATTRIBUTS ====================

    public function getNomCompletAttribute()
    {
        return $this->nom . ' ' . $this->prenoms;
    }

    public function getGradeLibelleAttribute()
    {
        return $this->grade?->libelle ?? $this->grade ?? 'Non renseigné';
    }

    public function getArmeeNomAttribute()
    {
        return $this->armee ?? ($this->armeeRelation?->nom ?? 'Non renseigné');
    }

    // ==================== MÉTHODES ====================

    /**
     * Normaliser le statut
     */
    public static function normaliserStatut($statut)
    {
        $statutMap = [
            'Actif' => 'En activité',
            'En activite' => 'En activité',
            'En activité' => 'En activité',
            'Inactif' => 'Non activite',
            'Non activite' => 'Non activite',
            'Non activité' => 'Non activite',
            'Retraité' => 'En retraite',
            'En retraite' => 'En retraite',
            'Radié' => 'Radié',
        ];
        return $statutMap[$statut] ?? 'En activité';
    }

    /**
     * Synchroniser les données du militaire dans toutes les procédures associées
     */
    public function syncWithProcedures()
    {
        \Log::info('=== SYNC WITH PROCEDURES ===');
        \Log::info('Militaire ID: ' . $this->id);

        // Récupérer toutes les entrées procedure_militaire liées à ce militaire
        $procedureMilitaires = ProcedureMilitaire::where('militaire_id', $this->id)->get();

        \Log::info('Procédures trouvées: ' . $procedureMilitaires->count());

        foreach ($procedureMilitaires as $pm) {
            $updated = false;
            $changes = [];

            // Mettre à jour le type_personnel
            if ($pm->type_personnel !== $this->type_personnel) {
                $pm->type_personnel = $this->type_personnel;
                $updated = true;
                $changes['type_personnel'] = ['ancien' => $pm->getOriginal('type_personnel'), 'nouveau' => $this->type_personnel];
            }

            // Mettre à jour les champs temporaires
            if ($pm->nom_temp !== $this->nom) {
                $pm->nom_temp = $this->nom;
                $updated = true;
                $changes['nom_temp'] = ['ancien' => $pm->getOriginal('nom_temp'), 'nouveau' => $this->nom];
            }
            if ($pm->prenom_temp !== $this->prenoms) {
                $pm->prenom_temp = $this->prenoms;
                $updated = true;
                $changes['prenom_temp'] = ['ancien' => $pm->getOriginal('prenom_temp'), 'nouveau' => $this->prenoms];
            }
            if ($pm->grade_temp !== $this->grade) {
                $pm->grade_temp = $this->grade;
                $updated = true;
                $changes['grade_temp'] = ['ancien' => $pm->getOriginal('grade_temp'), 'nouveau' => $this->grade];
            }
            if ($pm->matricule_temp !== $this->matricule) {
                $pm->matricule_temp = $this->matricule;
                $updated = true;
                $changes['matricule_temp'] = ['ancien' => $pm->getOriginal('matricule_temp'), 'nouveau' => $this->matricule];
            }
            if ($pm->profession_temp !== $this->profession) {
                $pm->profession_temp = $this->profession;
                $updated = true;
                $changes['profession_temp'] = ['ancien' => $pm->getOriginal('profession_temp'), 'nouveau' => $this->profession];
            }

            if ($updated) {
                $pm->save();
                \Log::info('ProcedureMilitaire #' . $pm->id . ' mis à jour:', $changes);
            }
        }

        return $this;
    }

    /**
     * Vérifier si le militaire a des procédures
     */
    public function hasProcedures()
    {
        return $this->procedureMilitaires()->exists() || $this->procedures()->exists();
    }

    /**
     * Récupérer toutes les procédures du militaire
     */
    public function getAllProcedures()
    {
        return Procedure::where('militaire_id', $this->id)
            ->orWhereHas('procedureMilitaires', function($q) {
                $q->where('militaire_id', $this->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}