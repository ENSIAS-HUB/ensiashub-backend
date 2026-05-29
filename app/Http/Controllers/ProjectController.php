<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // ── GET /api/users/{username}/projects ───────────────────────────
    public function index(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        return response()->json($user->projects()->get());
    }

    // ── POST /api/projects ───────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'tech_stack'  => 'nullable|array',
            'tech_stack.*'=> 'string|max:50',
            'github_url'  => 'nullable|url|max:255',
            'live_url'    => 'nullable|url|max:255',
            'image_url'   => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'status'      => 'nullable|in:en_cours,terminé,en_pause',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date',
        ]);

        $project = $request->user()->projects()->create($request->only([
            'titre', 'description', 'tech_stack', 'github_url', 'live_url',
            'image_url', 'is_featured', 'status', 'date_debut', 'date_fin',
        ]));

        $request->user()->logActivity('project_added', $project, "a ajouté le projet : {$project->titre}");

        return response()->json($project, 201);
    }

    // ── PUT /api/projects/{project} ──────────────────────────────────
    public function update(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id && !$request->user()->isAdminOrAbove()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'titre'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'tech_stack'  => 'nullable|array',
            'github_url'  => 'nullable|url|max:255',
            'live_url'    => 'nullable|url|max:255',
            'image_url'   => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'status'      => 'nullable|in:en_cours,terminé,en_pause',
            'date_debut'  => 'nullable|date',
            'date_fin'    => 'nullable|date',
        ]);

        $project->update($request->only([
            'titre', 'description', 'tech_stack', 'github_url', 'live_url',
            'image_url', 'is_featured', 'status', 'date_debut', 'date_fin',
        ]));

        return response()->json($project->fresh());
    }

    // ── DELETE /api/projects/{project} ───────────────────────────────
    public function destroy(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id && !$request->user()->isAdminOrAbove()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(['success' => true]);
    }

    // ── PATCH /api/projects/{project}/feature ────────────────────────
    public function toggleFeature(Request $request, Project $project): JsonResponse
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->update(['is_featured' => !$project->is_featured]);

        return response()->json(['is_featured' => $project->is_featured]);
    }
}
