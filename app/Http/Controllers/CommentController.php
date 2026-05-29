<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Relations\Relation;

class CommentController extends Controller
{
    // ── Résoudre le morph type → model class ─────────────────────────────────
    private function resolveModel(string $type): ?string
    {
        $map = Relation::morphMap();

        if (isset($map[$type])) {
            return $map[$type];
        }

        return null;
    }

    // ── GET /{type}/{id}/comments ─────────────────────────────────────────────
    public function index(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        $model = $modelClass::findOrFail($id);

        $comments = $model->comments()
                          ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $comments,
        ]);
    }

    // ── POST /{type}/{id}/comments ────────────────────────────────────────────
    public function store(Request $request, string $type, string $id): JsonResponse
    {
        $modelClass = $this->resolveModel($type);

        if (! $modelClass) {
            return response()->json(['success' => false, 'message' => 'Type inconnu'], 404);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $model = $modelClass::findOrFail($id);

        $comment = $model->allComments()->create([
            'user_id'  => $request->user()->id,
            'content'  => $validated['content'],
            'parent_id' => null,
        ]);

        $comment->load('user:id,nom,prenom,avatar_url,photoProfil');

        // Notify the content author
        if (isset($model->user_id) && $model->user_id !== $request->user()->id) {
            $author = \App\Models\User::find($model->user_id);
            if ($author) {
                $notifService = app(NotificationService::class);
                $contentTitle = $model->titre ?? $model->content ?? '';
                $notifService->notifyComment(
                    $author,
                    mb_strimwidth($contentTitle, 0, 80, '…'),
                );
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $comment,
        ], 201);
    }

    // ── POST /comments/{comment}/reply ────────────────────────────────────────
    public function reply(Request $request, Comment $comment): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $reply = Comment::create([
            'user_id'          => $request->user()->id,
            'commentable_type' => $comment->commentable_type,
            'commentable_id'   => $comment->commentable_id,
            'parent_id'        => $comment->id,
            'content'          => $validated['content'],
        ]);

        $reply->load('user:id,nom,prenom,avatar_url,photoProfil');

        return response()->json([
            'success' => true,
            'data'    => $reply,
        ], 201);
    }

    // ── PUT /comments/{comment} ───────────────────────────────────────────────
    public function update(Request $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->update(['content' => $validated['content']]);

        return response()->json([
            'success' => true,
            'data'    => $comment->fresh(['user:id,nom,prenom,avatar_url,photoProfil']),
        ]);
    }

    // ── DELETE /comments/{comment} ────────────────────────────────────────────
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();

        $isOwner     = $comment->user_id === $user->id;
        $isModerator = $user->isSuperAdmin()
                       || $user->hasRole('admin')
                       || $user->hasRole('chef_scolarite');

        if (! $isOwner && ! $isModerator) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Soft-delete pour garder le thread intact
        $comment->delete();

        return response()->json(['success' => true, 'message' => 'Commentaire supprimé']);
    }
}
