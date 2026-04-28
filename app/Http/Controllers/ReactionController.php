<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
            'reaction' => 'required|string|in:like,love,laugh,sad,angry',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier que la publication est validée
        $publication = Publication::find($request->publication_id);
        if ($publication->statutValidation !== 'Valide') {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de réagir à une publication non validée'
            ], 400);
        }

        // Vérifier si l'utilisateur a déjà réagi
        $existingReaction = Reaction::where('user_id', Auth::id())
                                    ->where('publication_id', $request->publication_id)
                                    ->first();
        
        if ($existingReaction) {
            // Mettre à jour la réaction existante
            $existingReaction->update(['reaction' => $request->reaction]);
            
            return response()->json([
                'success' => true,
                'message' => 'Réaction mise à jour',
                'data' => $existingReaction
            ]);
        }

        // Créer d'abord l'interaction parente
        $interaction = \App\Models\Interaction::create([
            'user_id' => Auth::id(),
            'publication_id' => $request->publication_id,
            'type' => 'reaction',
        ]);

        // Puis créer la réaction liée
        $reaction = Reaction::create([
            'id' => $interaction->id,
            'reaction' => $request->reaction,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réaction ajoutée',
            'data' => $reaction->load('user')
        ], 201);
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
}