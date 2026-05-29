<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Membership status for the authenticated user
        $membershipStatus = 'none';
        $isMember         = false;

        $userId = Auth::id();
        if ($userId && $this->relationLoaded('membres')) {
            $userMember = $this->membres->firstWhere('id', $userId);
            if ($userMember) {
                $statut          = $userMember->pivot->statut ?? 'EnAttente';
                $membershipStatus = match ($statut) {
                    'Approuve' => 'approved',
                    'EnAttente' => 'pending',
                    'Rejete'   => 'rejected',
                    'Banni'    => 'banned',
                    default    => 'none',
                };
                $isMember = ($statut === 'Approuve');
            }
        }

        // Resolve moderator (first membre with role Moderateur, else the creator)
        $moderator = null;
        if ($this->relationLoaded('membres')) {
            $modUser = $this->membres->first(fn ($m) => ($m->pivot->role ?? '') === 'Moderateur');
            if ($modUser) {
                $moderator = [
                    'id'     => $modUser->id,
                    'name'   => trim(($modUser->prenom ?? '') . ' ' . ($modUser->nom ?? '')),
                    'avatar' => $modUser->photoProfil ?? null,
                ];
            }
        }

        return [
            'id'                => $this->id,
            'name'              => $this->nom,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'category'          => strtolower($this->categorie),
            'filiere_key'       => $this->filiere_key,
            'annee_filiere'     => $this->annee_filiere,
            'avatar_url'        => $this->avatar_url,
            'cover_url'         => $this->cover_url,
            'cover_image'       => $this->cover_url,   // backward-compat alias
            'instagram_handle'  => $this->instagram_handle,
            'instagram_url'     => $this->instagram_url,
            'members_count'     => (int) ($this->membres_count ?? 0),
            'moderator'         => $moderator,
            'membership_status' => $membershipStatus,
            'is_member'         => $isMember,
            'auto_assigned'     => (bool) ($this->whenLoaded('membres')
                                    && $this->membres->firstWhere('id', Auth::id())?->pivot?->auto_assigned ?? false),
            'created_at'        => $this->created_at,
        ];
    }
}
