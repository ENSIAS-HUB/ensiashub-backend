<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'username',
        'avatar_url',
        'cover_url',
        'ville',
        'linkedin_url',
        'github_url',
        'website_url',
        'phone',
        'competences',
        'specialite',
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
            'competences' => 'array',
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
        $roles = $this->roles ?? [];
        return is_array($roles) && in_array($role, $roles, true);
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

    /** Cuisinier — gestion des commandes cuisine */
    public function isCuisinier(): bool
    {
        return $this->hasRole('cuisinier');
    }

    /** Peut accéder au dashboard cuisine */
    public function canAccessKitchen(): bool
    {
        return $this->isCuisinier() || $this->isSuperAdmin();
    }

    /**
     * Peut modérer n'importe quel groupe (sans être Moderateur dans la table pivot).
     * Utilisé pour les admins/superAdmins.
     */
    public function canManageAllGroups(): bool
    {
        return $this->isAdminOrAbove();
    }
    // ── Contextual role helpers ──────────────────────────────────────────

    /** Rôles contextuels de l'utilisateur (president_club, délégué ...) */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Est-il modérateur/président du groupe donné ?
     * Cela inclut :
     *  - avoir role='Modérateur' dans adhesion_groups pour ce groupe
     *  - OU avoir une entrée user_roles president_club pour ce groupe
     */
    public function isPresidentOf(string $groupId): bool
    {
        // Check via adhesion_groups pivot (Modérateur = président/animateur du club)
        $viaPivot = AdhesionGroup::where('user_id', $this->id)
            ->where('group_id', $groupId)
            ->where('role', 'Modérateur')
            ->where('statut', 'Approuve')
            ->exists();

        if ($viaPivot) return true;

        // Check via user_roles table (explicit assignment)
        return UserRole::where('user_id', $this->id)
            ->where('role', 'president_club')
            ->where('context_type', 'group')
            ->where('context_id', $groupId)
            ->exists();
    }

    /**
     * Est-il délégué du groupe filière donné ?
     */
    public function isDelegueOf(string $groupId): bool
    {
        return UserRole::where('user_id', $this->id)
            ->where('role', 'delegue')
            ->where('context_type', 'group')
            ->where('context_id', $groupId)
            ->exists();
    }

    /**
     * Peut gérer les demandes d'adhésion d'un groupe ?
     */
    public function canManageClub(string $groupId): bool
    {
        return $this->isAdminOrAbove() || $this->isPresidentOf($groupId);
    }

    // ── Profile relations ─────────────────────────────────────────────

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)
                    ->orderBy('is_featured', 'desc')
                    ->orderBy('ordre');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 'follows', 'following_id', 'follower_id'
        )->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class, 'follows', 'follower_id', 'following_id'
        )->withTimestamps();
    }

    public function activity(): HasMany
    {
        return $this->hasMany(ProfileActivity::class, 'user_id')->latest();
    }

    public function groupMembers(): HasMany
    {
        return $this->hasMany(AdhesionGroup::class, 'user_id');
    }

    // ── Profile helpers ───────────────────────────────────────────────

    public function isFollowedBy(User $user): bool
    {
        return Follow::where('follower_id', $user->id)
                     ->where('following_id', $this->id)
                     ->exists();
    }

    public function followersCount(): int
    {
        return Follow::where('following_id', $this->id)->count();
    }

    public function followingCount(): int
    {
        return Follow::where('follower_id', $this->id)->count();
    }

    public function logActivity(string $type, Model $subject, string $description = ''): void
    {
        ProfileActivity::create([
            'user_id'      => $this->id,
            'type'         => $type,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'description'  => $description,
        ]);
    }
}