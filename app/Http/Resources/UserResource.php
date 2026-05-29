<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $roles = $this->roles;
        $primaryRole = is_array($roles)
            ? ($roles[0] ?? 'etudiant')
            : ($roles ?? 'etudiant');

        // Contextual roles (president_club, delegue linked to a specific group)
        $contextualRoles = [];
        if ($this->relationLoaded('userRoles')) {
            $contextualRoles = $this->userRoles->map(fn ($ur) => [
                'role'         => $ur->role,
                'context_type' => $ur->context_type,
                'context_id'   => $ur->context_id,
            ])->values()->toArray();
        }

        return [
            'id'               => $this->id,
            'name'             => trim(($this->prenom ?? '') . ' ' . ($this->nom ?? '')),
            'prenom'           => $this->prenom,
            'nom'              => $this->nom,
            'username'         => $this->username,
            'email'            => $this->emailInstitutionnel,
            'avatar'           => $this->avatar_url ?? $this->photoProfil,
            'avatar_url'       => $this->avatar_url ?? $this->photoProfil,
            'role'             => $primaryRole,
            'roles'            => is_array($roles) ? $roles : [$primaryRole],
            'contextual_roles' => $contextualRoles,
            'filiere'          => $this->filiere,
            'annee'            => $this->annee,
            'bio'              => $this->bio,
            'created_at'       => $this->created_at,
        ];
    }
}
