<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==================== RÔLES ET PERMISSIONS ====================

    /**
     * Liste des rôles avec leurs libellés
     */
    public static function getRoles(): array
    {
        return [
            'SD' => 'Sous-Directeur',
            'CDD' => 'Chef de Division Disciplinaire',
            'CDS' => 'Chef de Section Statistique',
            'CDB' => 'Chef de Bureau Juridique',
            'ADS' => 'Agent de Saisie',
            'DIR' => 'Directeur', // NOUVEAU RÔLE - Lecture seule
        ];
    }

    /**
     * Obtenir le libellé du rôle
     */
    public function getRoleLabelAttribute(): string
    {
        return self::getRoles()[$this->role] ?? $this->role;
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur a l'un des rôles spécifiés
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Vérifier si l'utilisateur est en lecture seule
     */
    public function estLectureSeule(): bool
    {
        return $this->hasRole('DIR');
    }

    // ==================== PERMISSIONS ====================

    /**
     * Peut créer des procédures
     * DIR ne peut pas créer
     */
    public function peutCreerProcedure(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }

    /**
     * Peut modifier une procédure
     * DIR ne peut pas modifier
     */
    public function peutModifierProcedure(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }

    /**
     * Peut valider une phase
     * DIR ne peut pas valider
     */
    public function peutValiderPhase(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['CDS', 'CDD', 'SD']);
    }

    /**
     * Peut supprimer une procédure
     * DIR ne peut pas supprimer, seul SD le peut
     */
    public function peutSupprimerProcedure(): bool
    {
        return $this->hasRole('SD');
    }

    /**
     * Peut consulter toutes les procédures
     * DIR peut consulter tout (lecture seule)
     */
    public function peutConsulterTout(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB', 'DIR']);
    }

    /**
     * Peut gérer les utilisateurs
     * Seul SD le peut
     */
    public function peutGererUtilisateurs(): bool
    {
        return $this->hasRole('SD');
    }

    /**
     * Peut exporter en PDF
     * DIR peut exporter (lecture seule mais export autorisé)
     */
    public function peutExporter(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB', 'DIR']);
    }

    /**
     * Peut voir le dashboard
     * Tous les utilisateurs, y compris DIR
     */
    public function peutVoirDashboard(): bool
    {
        return true;
    }

    /**
     * Peut consulter l'historique
     * DIR peut consulter l'historique
     */
    public function peutVoirHistorique(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'DIR']);
    }

    /**
     * Peut gérer les infractions
     * DIR ne peut pas gérer les infractions
     */
    public function peutGererInfractions(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['CDS', 'CDD', 'SD']);
    }

    /**
     * Peut gérer les militaires
     * DIR ne peut pas gérer les militaires
     */
    public function peutGererMilitaires(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }

    /**
     * Peut voir les statistiques (Dashboard)
     * Tous les utilisateurs, y compris DIR
     */
    public function peutVoirStatistiques(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB', 'DIR']);
    }

    /**
     * Peut modifier les paramètres système
     * Seul SD le peut
     */
    public function peutModifierSysteme(): bool
    {
        return $this->hasRole('SD');
    }

    /**
     * Peut voir les rapports
     * DIR peut voir les rapports
     */
    public function peutVoirRapports(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB', 'DIR']);
    }

    /**
     * Peut créer des rapports
     * DIR ne peut pas créer de rapports
     */
    public function peutCreerRapports(): bool
    {
        if ($this->estLectureSeule()) {
            return false;
        }
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB']);
    }
}