<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\AdhesionGroup;
use App\Models\Group;
use App\Models\User;
use App\Services\AutoGroupAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // ── GET /api/profile (mon propre profil) ──────────────────────────
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return $this->profileResponse($user, $user);
    }

    // ── GET /api/users/{username}/profile ────────────────────────────
    public function show(Request $request, string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();
        return $this->profileResponse($target, $request->user());
    }

    // ── GET /api/users/{username}/projects ───────────────────────────
    public function projects(string $username): JsonResponse
    {
        $user     = User::where('username', $username)->firstOrFail();
        $projects = $user->projects()->get();
        return response()->json($projects);
    }

    // ── GET /api/users/{username}/activity ───────────────────────────
    public function activity(string $username): JsonResponse
    {
        $user       = User::where('username', $username)->firstOrFail();
        $activities = $user->activity()->paginate(20);
        return response()->json($activities);
    }

    // ── GET /api/users/{username}/followers ──────────────────────────
    public function followers(Request $request, string $username): JsonResponse
    {
        $user      = User::where('username', $username)->firstOrFail();
        $authUser  = $request->user();
        $followers = $user->followers()
            ->select('users.id', 'users.nom', 'users.prenom', 'users.username',
                     'users.avatar_url', 'users.photoProfil', 'users.filiere', 'users.annee')
            ->paginate(20);

        $followers->getCollection()->transform(function ($f) use ($authUser) {
            $f->is_following = $authUser ? $f->isFollowedBy($authUser) : false;
            $f->name         = trim(($f->prenom ?? '') . ' ' . ($f->nom ?? ''));
            return $f;
        });

        return response()->json($followers);
    }

    // ── GET /api/users/{username}/following ──────────────────────────
    public function following(Request $request, string $username): JsonResponse
    {
        $user      = User::where('username', $username)->firstOrFail();
        $authUser  = $request->user();
        $following = $user->following()
            ->select('users.id', 'users.nom', 'users.prenom', 'users.username',
                     'users.avatar_url', 'users.photoProfil', 'users.filiere', 'users.annee')
            ->paginate(20);

        $following->getCollection()->transform(function ($f) use ($authUser) {
            $f->is_following = $authUser ? $f->isFollowedBy($authUser) : false;
            $f->name         = trim(($f->prenom ?? '') . ' ' . ($f->nom ?? ''));
            return $f;
        });

        return response()->json($following);
    }

    // ── PUT /api/profile ──────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $v = Validator::make($request->all(), [
            'username'      => "nullable|string|unique:users,username,{$user->id}|min:3|max:30|regex:/^[a-z0-9._]+$/",
            'bio'           => 'nullable|string|max:500',
            'ville'         => 'nullable|string|max:100',
            'linkedin_url'  => 'nullable|url|max:255',
            'github_url'    => 'nullable|url|max:255',
            'website_url'   => 'nullable|url|max:255',
            'specialite'    => 'nullable|string|max:100',
            'competences'   => 'nullable|array',
            'competences.*' => 'string|max:50',
            'phone'         => 'nullable|string|max:20',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $user->update($v->validated());

        return response()->json(['success' => true, 'user' => $user->fresh()]);
    }

    // ── PUT /api/profile/password ─────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        // OAuth-only accounts have no local password
        if ($user->provider && !$user->password) {
            return response()->json([
                'success' => false,
                'message' => 'Changement de mot de passe non disponible pour ce type de compte.',
            ], 422);
        }

        $v = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => ['current_password' => ['Mot de passe actuel incorrect.']],
            ], 422);
        }

        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->new_password)]);

        return response()->json(['success' => true, 'message' => 'Mot de passe mis à jour avec succès.']);
    }

    // ── POST /api/profile/avatar ──────────────────────────────────────
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $user = $request->user();

        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        $user->update(['avatar_url' => '/storage/' . $path]);

        return response()->json(['avatar_url' => $user->avatar_url]);
    }

    // ── DELETE /api/profile/avatar ───────────────────────────────────
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
        }

        $user->update(['avatar_url' => null]);

        return response()->json(['message' => 'Avatar supprimé', 'avatar_url' => null]);
    }

    // ── POST /api/profile/cover ───────────────────────────────────────
    public function updateCover(Request $request): JsonResponse
    {
        $request->validate(['cover' => 'required|image|max:5120']);
        $user = $request->user();

        $path = $request->file('cover')->store("covers/{$user->id}", 'public');
        $user->update(['cover_url' => '/storage/' . $path]);

        return response()->json(['cover_url' => $user->cover_url]);
    }

    // ── DELETE /api/profile/cover ─────────────────────────────────────
    public function deleteCover(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->cover_url && str_starts_with($user->cover_url, '/storage/')) {
            $relativePath = ltrim($user->cover_url, '/storage/');
            \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
        }

        $user->update(['cover_url' => null]);

        return response()->json(['message' => 'Bannière supprimée', 'cover_url' => null]);
    }

    // ── PATCH /me/complete-profile ────────────────────────────────────
    public function completeProfile(Request $request): JsonResponse
    {
        $filieres = ['GL', 'GD', 'D2S', '2IA', '2SCL', 'SSE', 'CSCMC', 'IDF', 'BI&A', 'EITC', 'INSEC'];
        $annees   = ['1A', '2A', '3A'];

        $validator = Validator::make($request->all(), [
            'filiere' => ['required', 'string', 'in:' . implode(',', $filieres)],
            'annee'   => ['required', 'string', 'in:' . implode(',', $annees)],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if ($user->filiere && $user->annee) {
            return response()->json(['success' => false, 'message' => 'Profil déjà complété.'], 409);
        }

        $user->update(['filiere' => $request->filiere, 'annee' => $request->annee]);
        app(AutoGroupAssignmentService::class)->assignUserToFiliereGroup($user->fresh());

        return response()->json([
            'success' => true,
            'message' => 'Profil complété avec succès.',
            'data'    => ['filiere' => $user->filiere, 'annee' => $user->annee],
        ]);
    }

    // ── Private helper ────────────────────────────────────────────────
    private function profileResponse(User $user, ?User $authUser): JsonResponse
    {
        $followersCount = $user->followersCount();
        $followingCount = $user->followingCount();
        $projectsCount  = $user->projects()->count();
        $isOwnProfile   = $authUser && $authUser->id === $user->id;

        // Contextual roles
        $contextualRoles = $user->userRoles()
            ->get()
            ->map(function ($ur) {
                $contextName = null;
                if ($ur->context_type === 'group' && $ur->context_id) {
                    $contextName = Group::find($ur->context_id)?->nom;
                }
                return ['role' => $ur->role, 'context_name' => $contextName];
            });

        // Groups
        $adhesions = AdhesionGroup::where('user_id', $user->id)
            ->where('statut', 'Approuve')
            ->with('group')
            ->get();

        $filiereAdhesion = $adhesions->first(fn($a) => $a->group && $a->group->categorie === 'Filiere');
        $filiereGroup    = $filiereAdhesion?->group;
        $clubs           = $adhesions->filter(fn($a) => $a->group && $a->group->categorie === 'Club')
                                     ->pluck('group')
                                     ->values();

        $mapGroup = fn($g) => $g ? [
            'id'           => $g->id,
            'name'         => $g->nom,
            'slug'         => $g->slug,
            'avatar_url'   => $g->avatar_url,
            'description'  => $g->description,
            'category'     => strtolower($g->categorie),
            'members_count'=> 0,
            'created_at'   => $g->created_at,
        ] : null;

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'username'        => $user->username,
                'name'            => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                'avatar_url'      => $user->avatar_url,
                'cover_url'       => $user->cover_url,
                'bio'             => $user->bio,
                'filiere'         => $user->filiere,
                'annee'           => $user->annee,
                'specialite'      => $user->specialite,
                'ville'           => $user->ville,
                'linkedin_url'    => $user->linkedin_url,
                'github_url'      => $user->github_url,
                'website_url'     => $user->website_url,
                'competences'     => $user->competences ?? [],
                'role'            => is_array($user->roles) ? ($user->roles[0] ?? 'etudiant') : 'etudiant',
                'contextual_roles'=> $contextualRoles->values(),
                'followers_count' => $followersCount,
                'following_count' => $followingCount,
                'projects_count'  => $projectsCount,
                'is_following'    => ($authUser && !$isOwnProfile) ? $user->isFollowedBy($authUser) : false,
                'is_own_profile'  => $isOwnProfile,
                'filiere_group'   => $mapGroup($filiereGroup),
                'clubs'           => $clubs->map($mapGroup)->values(),
            ],
        ]);
    }
}
