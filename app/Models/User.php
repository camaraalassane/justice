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
            'ADMIN' => 'Administrateur',
            'CDD' => 'Chef de Division Disciplinaire',
            'CDS' => 'Chef de Section Statistique',
            'CDB' => 'Chef de Bureau Juridique',
            'ADS' => 'Agent de Saisie',
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
     * Vérifier si l'utilisateur est en lecture seule (obsolète, aucun rôle en lecture seule)
     */
    public function estLectureSeule(): bool
    {
        return false;
    }

    // ==================== PERMISSIONS ====================

    /**
     * Peut créer des procédures
     */
    public function peutCreerProcedure(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut modifier une procédure
     */
    public function peutModifierProcedure(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut valider une phase
     */
    public function peutValiderPhase(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut supprimer une procédure
     * Seul l'Administrateur le peut
     */
    public function peutSupprimerProcedure(): bool
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Peut consulter toutes les procédures
     */
    public function peutConsulterTout(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN', 'CDB']);
    }

    /**
     * Peut gérer les utilisateurs
     * Seul l'Administrateur le peut
     */
    public function peutGererUtilisateurs(): bool
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Peut exporter en PDF
     */
    public function peutExporter(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN', 'CDB']);
    }

    /**
     * Peut voir le dashboard
     */
    public function peutVoirDashboard(): bool
    {
        return true;
    }

    /**
     * Peut consulter l'historique
     */
    public function peutVoirHistorique(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut gérer les infractions
     */
    public function peutGererInfractions(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut gérer les militaires
     */
    public function peutGererMilitaires(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'ADMIN']);
    }

    /**
     * Peut voir les statistiques (Dashboard)
     */
    public function peutVoirStatistiques(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN', 'CDB']);
    }

    /**
     * Peut modifier les paramètres système
     * Seul l'Administrateur le peut
     */
    public function peutModifierSysteme(): bool
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Peut voir les rapports
     */
    public function peutVoirRapports(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN', 'CDB']);
    }

    /**
     * Peut créer des rapports
     */
    public function peutCreerRapports(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'ADMIN', 'CDB']);
    }
}