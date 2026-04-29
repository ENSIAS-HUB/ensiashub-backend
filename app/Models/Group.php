<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Group extends Model
{
    use HasUuids;

    protected $table = 'groups';

    protected $fillable = [
        'nom',
        'categorie',
        'description',
        'createur_id',
        'creeLe',
    ];

    protected $casts = [
        'categorie' => 'string',
        'creeLe' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec les membres (utilisateurs) via AdhesionGroup
     */
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'adhesion_groups')
                    ->withPivot('statut', 'role', 'joinedAt', 'reviewedAt', 'motifDecision')
                    ->withTimestamps();
    }

    /**
     * Relation avec les publications du groupe
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class, 'groupe_id');
    }

    /**
     * Ajouter un membre au groupe
     */
    public function ajouterMembre(User $user, string $role = 'Membre'): array
    {
        $existant = $this->membres()->where('user_id', $user->id)->first();
        
        if ($existant) {
            return [
                'success' => false,
                'message' => 'Cet utilisateur est déjà dans le groupe ou en attente.'
            ];
        }

        $this->membres()->attach($user, [
            'statut' => 'EnAttente',
            'role' => $role,
            'joinedAt' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Demande d\'adhésion envoyée avec succès.'
        ];
    }

    /**
     * Valider un membre
     */
    public function validerMembre(User $user): array
    {
        $adhesion = $this->membres()->where('user_id', $user->id)->first();
        
        if (!$adhesion) {
            return [
                'success' => false,
                'message' => 'Cet utilisateur n\'a pas demandé à rejoindre le groupe.'
            ];
        }

        if ($adhesion->pivot->statut !== 'EnAttente') {
            return [
                'success' => false,
                'message' => 'Cette demande a déjà été traitée.'
            ];
        }

        $this->membres()->updateExistingPivot($user, [
            'statut' => 'Approuve',
            'reviewedAt' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Membre validé avec succès.'
        ];
    }

    /**
     * Vérifier si un utilisateur est modérateur
     */
    public function estModerateur(?User $user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        $adhesion = $this->membres()
                        ->where('user_id', $user->id)
                        ->wherePivot('statut', 'Approuve')
                        ->first();
        
        return $adhesion && $adhesion->pivot->role === 'Moderateur';
    }

    /**
     * Vérifier si un utilisateur est membre
     */
    public function estMembre(?User $user = null): bool
    {
        if (!$user) {
            $user = Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        return $this->membres()
                    ->where('user_id', $user->id)
                    ->wherePivot('statut', 'Approuve')
                    ->exists();
    }

    /**
     * Obtenir les membres approuvés
     */
    public function getMembresApprouves()
    {
        return $this->membres()->wherePivot('statut', 'Approuve')->get();
    }

    /**
     * Obtenir les demandes en attente
     */
    public function getDemandesEnAttente()
    {
        return $this->membres()->wherePivot('statut', 'EnAttente')->get();
    }

    /**
     * Rechercher des groupes
     */
    public static function rechercher(string $searchTerm)
    {
        return self::where('nom', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orderBy('nom')
                    ->get();
    }
}