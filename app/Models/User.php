<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'emailInstitutionnel',
        'nom',
        'prenom',
        'password',
        'provider',    
        'provider_id',   
        'photoProfil',
        'bio',
        'profileActif',
        'roles',
        'filiere',
        'annee',
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
            'profileActif' => 'boolean',
            'roles' => 'array', 
        ];
    }

    public function modifierProfile(array $data)
    {
        return $this->update($data);
    }

    // ── Helpers de rôles ───────────────────────────────────────────────

    /**
     * Vérifie si l'utilisateur possède un rôle donné.
     * Exemple : $user->hasRole('superAdmin')
     */
    public function hasRole(string $role): bool
    {
        return is_array($this->roles) && in_array($role, $this->roles);
    }

    /** superAdmin OU admin */
    public function isAdminOrAbove(): bool
    {
        return $this->hasRole('superAdmin') || $this->hasRole('admin');
    }

    /** superAdmin uniquement */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('superAdmin');
    }

    /**
     * Peut modérer n'importe quel groupe (sans être Moderateur dans la table pivot).
     * Utilisé pour les admins/superAdmins.
     */
    public function canManageAllGroups(): bool
    {
        return $this->isAdminOrAbove();
    }
}