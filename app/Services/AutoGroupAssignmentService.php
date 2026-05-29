<?php

namespace App\Services;

use App\Models\AdhesionGroup;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AutoGroupAssignmentService
{
    /**
     * Assign (or re-assign) a user to their filière group automatically.
     * Idempotent — safe to call multiple times.
     */
    public function assignUserToFiliereGroup(User $user): void
    {
        if (!$user->filiere) {
            return;
        }

        $group = $this->resolveGroup($user);

        if (!$group) {
            Log::warning("AutoGroupAssignment: no group found", [
                'user_id' => $user->id,
                'filiere' => $user->filiere,
                'annee'   => $user->annee,
            ]);
            return;
        }

        // Remove previous auto-assigned filière memberships (user changed filière)
        $this->removePreviousFiliereGroups($user, $group->id);

        // Idempotent upsert
        AdhesionGroup::firstOrCreate(
            ['user_id' => $user->id, 'group_id' => $group->id],
            [
                'statut'        => 'Approuve',
                'role'          => 'Membre',
                'joinedAt'      => now(),
                'reviewedAt'    => now(),
                'auto_assigned' => true,
            ]
        );
    }

    private function resolveGroup(User $user): ?Group
    {
        // 1. Match by filiere_key + annee_filiere (new columns, most precise)
        $query = Group::where('categorie', 'Filiere')
            ->where('filiere_key', $user->filiere);

        if ($user->annee) {
            $group = (clone $query)->where('annee_filiere', $user->annee)->first();
            if ($group) return $group;

            // 2. Fallback: old nom-based matching ("IDF 2A")
            $group = Group::where('categorie', 'Filiere')
                ->where('nom', $user->filiere . ' ' . $user->annee)
                ->first();
            if ($group) return $group;
        }

        // 3. Year-independent groups (EITC, INSEC — no annee_filiere)
        // Only fall through to this if the user genuinely has no annee set.
        if (!$user->annee) {
            return (clone $query)->whereNull('annee_filiere')->first();
        }

        return null;
    }

    private function removePreviousFiliereGroups(User $user, string $keepGroupId): void
    {
        $otherFiliereIds = Group::where('categorie', 'Filiere')
            ->where('id', '!=', $keepGroupId)
            ->pluck('id');

        // Remove ALL filière memberships for this user (regardless of auto_assigned flag).
        // A user can only belong to one filière group at a time, so stale rows — including
        // old records created before the auto_assigned column existed — must be deleted.
        AdhesionGroup::where('user_id', $user->id)
            ->whereIn('group_id', $otherFiliereIds)
            ->delete();
    }
}
