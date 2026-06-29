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
            'CDD' => 'Chef de Division',
            'CDS' => 'Chef de Section',
            'CDB' => 'Chef de Bureau',
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

    // ==================== PERMISSIONS ====================

    /**
     * Peut créer des procédures
     */
    public function peutCreerProcedure(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }

    /**
     * Peut valider une phase (CDS, CDD, SD)
     */
    public function peutValiderPhase(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD']);
    }

    /**
     * Peut consulter toutes les procédures
     */
    public function peutConsulterTout(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB']);
    }

    /**
     * Peut modifier une procédure (ADS, CDS, CDD, SD)
     */
    public function peutModifierProcedure(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }

    /**
     * Peut supprimer une procédure (CDD, SD)
     */
    public function peutSupprimerProcedure(): bool
    {
        return $this->hasAnyRole(['CDD', 'SD']);
    }

    /**
     * Peut gérer les utilisateurs (SD uniquement)
     */
    public function peutGererUtilisateurs(): bool
    {
        return $this->hasRole('SD');
    }

    /**
     * Peut exporter en PDF
     */
    public function peutExporter(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD', 'CDB']);
    }

    /**
     * Peut voir le dashboard (tous les utilisateurs)
     */
    public function peutVoirDashboard(): bool
    {
        return true;
    }

    /**
     * Peut consulter l'historique (CDS, CDD, SD)
     */
    public function peutVoirHistorique(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD']);
    }

    /**
     * Peut gérer les infractions (CDS, CDD, SD)
     */
    public function peutGererInfractions(): bool
    {
        return $this->hasAnyRole(['CDS', 'CDD', 'SD']);
    }

    /**
     * Peut gérer les militaires (CDS, CDD, SD, ADS)
     */
    public function peutGererMilitaires(): bool
    {
        return $this->hasAnyRole(['ADS', 'CDS', 'CDD', 'SD']);
    }
}