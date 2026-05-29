<?php

namespace App\Http\Middleware;

use App\Services\DriveQueryFilter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: gate for all Drive routes.
 *
 * - cuisinier / buvette          → 403 Forbidden
 * - superAdmin / admin / scolarite / chef_scolarite → pass through
 * - others (etudiant, etc.)      → require filiere + annee to be set
 *   otherwise 403 PROFILE_INCOMPLETE
 */
class DriveAccessMiddleware
{
    private const BLOCKED_ROLES = ['cuisinier', 'buvette'];

    public function __construct(private DriveQueryFilter $filter) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Block roles that have no drive access at all
        foreach (self::BLOCKED_ROLES as $role) {
            if ($user->hasRole($role)) {
                return response()->json(['error' => 'Accès non autorisé au Drive.'], 403);
            }
        }

        // Full-access roles bypass profile checks
        if ($this->filter->isFullAccess($user)) {
            return $next($request);
        }

        // Students / delegates / etc. must have a complete profile
        if (!$this->filter->hasCompleteProfile($user)) {
            return response()->json([
                'error' => 'PROFILE_INCOMPLETE',
                'message' => 'Veuillez compléter votre profil (filière et année) pour accéder au Drive.',
            ], 403);
        }

        return $next($request);
    }
}
