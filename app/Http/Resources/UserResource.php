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

        return [
            'id'         => $this->id,
            'name'       => trim(($this->prenom ?? '') . ' ' . ($this->nom ?? '')),
            'prenom'     => $this->prenom,
            'nom'        => $this->nom,
            'email'      => $this->emailInstitutionnel,
            'avatar'     => $this->photoProfil,
            'role'       => $primaryRole,
            'roles'      => is_array($roles) ? $roles : [$primaryRole],
            'filiere'    => $this->filiere,
            'annee'      => $this->annee,
            'bio'        => $this->bio,
            'created_at' => $this->created_at,
        ];
    }
}
