<?php

namespace App\Http\Middleware;

use App\Models\Group;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGroupMember
{
    /**
     * Deny access to non-members of a group identified by {groupId} route param.
     * The route must expose the group id as {groupId}.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $groupId = $request->route('groupId');

        if (!$groupId) {
            return response()->json(['message' => 'Group not specified.'], 400);
        }

        $user  = $request->user();
        $group = Group::find($groupId);

        if (!$group) {
            return response()->json(['message' => 'Group not found.'], 404);
        }

        $isMember = $group->membres()
            ->where('user_id', $user->id)
            ->wherePivot('statut', 'Approuve')
            ->exists();

        if (!$isMember) {
            return response()->json(['message' => 'You are not a member of this group.'], 403);
        }

        return $next($request);
    }
}
