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
            'description'       => $this->description,
            'category'          => strtolower($this->categorie),
            'cover_image'       => null,
            'members_count'     => (int) ($this->membres_count ?? 0),
            'moderator'         => $moderator,
            'membership_status' => $membershipStatus,
            'is_member'         => $isMember,
            'created_at'        => $this->created_at,
        ];
    }
}
