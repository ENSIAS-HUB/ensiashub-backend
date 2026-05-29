<?php

namespace App\Services;

use App\Models\Annee;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class DriveQueryFilter
{
    private const FULL_ACCESS_ROLES = ['superAdmin', 'admin', 'scolarite', 'chef_scolarite'];
    private const LEGACY_FILIERE_MAP = ['2AI' => '2IA', '2SCL' => '2CSL'];

    // ── Role checks ───────────────────────────────────────────────────────────

    public function isFullAccess(User $user): bool
    {
        foreach (self::FULL_ACCESS_ROLES as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasCompleteProfile(User $user): bool
    {
        return !empty($user->filiere) && !empty($user->annee);
    }

    // ── Filiere / Annee resolution from user string fields ────────────────────

    public function resolveUserFiliere(User $user): ?Filiere
    {
        $raw     = $user->filiere ?? '';
        $mapped  = self::LEGACY_FILIERE_MAP[$raw] ?? $raw;

        return Filiere::where('nom', $mapped)
                      ->orWhere('slug', Str::slug($mapped))
                      ->first();
    }

    public function resolveUserAnnee(User $user): ?Annee
    {
        $raw   = $user->annee ?? '1A';
        $annee = Annee::where('label', $raw)->first();

        if (!$annee) {
            $niveau = (int) preg_replace('/\D/', '', $raw);
            $annee  = $niveau ? Annee::where('niveau', $niveau)->first() : null;
        }

        return $annee;
    }

    // ── Query scoping ─────────────────────────────────────────────────────────

    /**
     * Apply filiere + annee filter to a modules query for the given user.
     */
    public function applyToModules(Builder $query, User $user): Builder
    {
        if ($this->isFullAccess($user)) {
            return $query;
        }

        $filiere = $this->resolveUserFiliere($user);
        $annee   = $this->resolveUserAnnee($user);

        if (!$filiere || !$annee) {
            // No results for incomplete profiles
            return $query->whereRaw('1 = 0');
        }

        if ($annee->niveau === 1) {
            $troncIds   = Filiere::where('is_tronc_commun', true)->where('is_active', true)->pluck('id')->toArray();
            $filiereIds = array_unique(array_merge([$filiere->id], $troncIds));
            return $query->whereIn('filiere_id', $filiereIds)->where('annee_id', $annee->id);
        }

        return $query->where('filiere_id', $filiere->id)->where('annee_id', $annee->id);
    }

    // ── Access checks ─────────────────────────────────────────────────────────

    public function canAccessModule(User $user, Module $module): bool
    {
        if ($this->isFullAccess($user)) {
            return true;
        }

        $filiere = $this->resolveUserFiliere($user);
        $annee   = $this->resolveUserAnnee($user);

        if (!$filiere || !$annee) {
            return false;
        }

        if ($annee->niveau === 1) {
            $troncIds   = Filiere::where('is_tronc_commun', true)->where('is_active', true)->pluck('id')->toArray();
            $filiereIds = array_unique(array_merge([$filiere->id], $troncIds));
            return in_array($module->filiere_id, $filiereIds) && $module->annee_id === $annee->id;
        }

        return $module->filiere_id === $filiere->id && $module->annee_id === $annee->id;
    }

    public function canAccessDocument(User $user, Document $document): bool
    {
        if ($this->isFullAccess($user)) {
            return true;
        }

        // Load the module chain if not already loaded
        if (!$document->relationLoaded('elementModule')) {
            $document->load('elementModule.module');
        }

        $module = $document->elementModule?->module ?? null;
        if (!$module) {
            return false;
        }

        return $this->canAccessModule($user, $module);
    }
}
