<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    // ── POST /api/users/{username}/follow ────────────────────────────
    public function follow(Request $request, string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();
        $me     = $request->user();

        if ($me->id === $target->id) {
            return response()->json(['error' => 'Vous ne pouvez pas vous suivre vous-même'], 422);
        }

        Follow::firstOrCreate([
            'follower_id'  => $me->id,
            'following_id' => $target->id,
        ]);

        return response()->json([
            'success'         => true,
            'followers_count' => $target->fresh()->followersCount(),
        ]);
    }

    // ── DELETE /api/users/{username}/follow ──────────────────────────
    public function unfollow(Request $request, string $username): JsonResponse
    {
        $target = User::where('username', $username)->firstOrFail();

        Follow::where('follower_id', $request->user()->id)
              ->where('following_id', $target->id)
              ->delete();

        return response()->json([
            'success'         => true,
            'followers_count' => $target->fresh()->followersCount(),
        ]);
    }

    // ── GET /api/suggestions ─────────────────────────────────────────
    public function suggestions(Request $request): JsonResponse
    {
        $me = $request->user();

        $followingIds = Follow::where('follower_id', $me->id)
                              ->pluck('following_id')
                              ->push($me->id);

        $suggestions = User::whereNotIn('id', $followingIds)
            ->when($me->filiere, fn($q) => $q->where('filiere', $me->filiere))
            ->whereNotNull('username')
            ->select('id', 'nom', 'prenom', 'username', 'avatar_url', 'photoProfil', 'filiere', 'annee')
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(fn($u) => array_merge($u->toArray(), [
                'name'       => trim(($u->prenom ?? '') . ' ' . ($u->nom ?? '')),
                'avatar_url' => $u->avatar_url ?? $u->photoProfil,
            ]));

        return response()->json(['suggestions' => $suggestions]);
    }
}
