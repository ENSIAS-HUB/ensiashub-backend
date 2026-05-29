<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Interaction;
use App\Models\Publication;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\Relation;

class ReactionController extends Controller
{
    /**
     * Afficher les réactions d'une publication
     * GET /api/publications/{publication_id}/reactions
     */
    public function index(Request $request)
    {
        $query = Reaction::with(['user']);
        
        if ($request->has('publication_id')) {
            $query->where('publication_id', $request->publication_id);
        }
        
        if ($request->has('type')) {
            $query->where('reaction', $request->type);
        }
        
        $reactions = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $reactions
        ]);
    }

    /**
     * Ajouter ou modifier une réaction
     * POST /api/reactions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'publication_id' => 'required|exists:publications,id',
            'reaction'       => 'nullable|string|in:like,love,laugh,sad,angry',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reactionType = $request->input('reaction', 'like');

        // Check for existing reaction via interactions table
        $existingInteraction = Interaction::where('user_id', Auth::id())
            ->where('publication_id', $request->publication_id)
            ->where('type', 'reaction')
            ->first();

        if ($existingInteraction) {
            // Toggle off — delete (cascades to reactions table)
            $existingInteraction->delete();
            return response()->json(['success' => true, 'message' => 'Réaction supprimée', 'reacted' => false]);
        }

        // Create parent interaction
        $interaction = Interaction::create([
            'user_id'        => Auth::id(),
            'publication_id' => $request->publication_id,
            'type'           => 'reaction',
        ]);

        // Create child reaction
        Reaction::create([
            'id'       => $interaction->id,
            'reaction' => $reactionType,
        ]);

        return response()->json(['success' => true, 'message' => 'Réaction ajoutée', 'reacted' => true], 201);
    }

    /**
     * Afficher une réaction spécifique
     * GET /api/reactions/{id}
     */
    public function show(string $id)
    {
        $reaction = Reaction::with(['user', 'publication'])->find($id);
        
        if (!$reaction) {
            return response()->json([
                'success' => false,
                'message' => 'Réaction non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reaction
        ]);
    }

    /**
     * Supprimer une réaction
     * DELETE /api/reactions/{id}
     */
    public function destroy(string $id)
    {
        $reaction = Reaction::find($id);
        
        if (!$reaction) {
            return response()->json([
                'success' => false,
                'message' => 'Réaction non trouvée'
            ], 404);
        }

        // Seul l'auteur peut supprimer sa réaction
        if ($reaction->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Supprimer l'interaction parente (cascade supprimera la réaction)
        $reaction->interaction()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Réaction supprimée'
        ]);
    }

    /**
     * Obtenir les statistiques des réactions d'une publication
     * GET /api/publications/{publication_id}/reactions/stats
     */
    public function stats(string $publicationId)
    {
        $publication = Publication::find($publicationId);
        
        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication non trouvée'
            ], 404);
        }

        $stats = [
            'total' => $publication->reactions()->count(),
            'like' => $publication->reactions()->where('reaction', 'like')->count(),
            'love' => $publication->reactions()->where('reaction', 'love')->count(),
            'laugh' => $publication->reactions()->where('reaction', 'laugh')->count(),
            'sad' => $publication->reactions()->where('reaction', 'sad')->count(),
            'angry' => $publication->reactions()->where('reaction', 'angry')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * POST /api/{type}/{id}/react — polymorphic reaction toggle
     */
    public function react(Request $request, string $type, string $id): JsonResponse
    {
        $map   = Relation::morphMap();
        $class = $map[$type] ?? null;

        if (!$class) {
            return response()->json(['success' => false, 'message' => 'Type non supporté'], 404);
        }

        $class::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reaction' => 'nullable|string|in:like,love,laugh,sad,angry',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $reactionType = $request->input('reaction', 'like');

        $existingInteraction = Interaction::where('user_id', Auth::id())
            ->where('reactable_type', $class)
            ->where('reactable_id', $id)
            ->where('type', 'reaction')
            ->first();

        if ($existingInteraction) {
            $existingInteraction->delete();
            $reacted = false;
        } else {
            $interaction = Interaction::create([
                'user_id'        => Auth::id(),
                'publication_id' => $type === 'publications' ? $id : null,
                'reactable_type' => $class,
                'reactable_id'   => $id,
                'type'           => 'reaction',
            ]);

            Reaction::create([
                'id'       => $interaction->id,
                'reaction' => $reactionType,
            ]);

            $reacted = true;

            // Notify the content author
            $model = $class::find($id);
            if ($model && isset($model->user_id) && $model->user_id !== Auth::id()) {
                $author = \App\Models\User::find($model->user_id);
                if ($author) {
                    $notifService = app(NotificationService::class);
                    $title = $model->titre ?? $model->content ?? '';
                    $notifService->notifyReaction(
                        $author,
                        $reactionType,
                        mb_strimwidth($title, 0, 80, '…'),
                    );
                }
            }
        }

        $count = Interaction::where('reactable_type', $class)
            ->where('reactable_id', $id)
            ->where('type', 'reaction')
            ->count();

        return response()->json([
            'success'         => true,
            'reacted'         => $reacted,
            'reactions_count' => $count,
            'user_emoji'      => $reacted ? $reactionType : null,
        ]);
    }
}