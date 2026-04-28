<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentaireController extends Controller
{
    /**
     * Afficher les commentaires d'une publication
     * GET /api/publications/{publication_id}/commentaires
     */
    public function index(Request $request)
    {
        $query = Commentaire::with(['user']);
        
        if ($request->has('publication_id')) {
            $query->where('publication_id', $request->publication_id);
        }
        
        $commentaires = $query->orderBy('created_at', 'asc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $commentaires
        ]);
    }

    /**
     * Créer un nouveau commentaire
     * POST /api/commentaires
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
            'publication_id' => 'required|exists:publications,id',
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
                'message' => 'Impossible de commenter une publication non validée'
            ], 400);
        }

        // Créer d'abord l'interaction parente
        $interaction = \App\Models\Interaction::create([
            'user_id' => Auth::id(),
            'publication_id' => $request->publication_id,
            'type' => 'commentaire',
        ]);

        // Puis créer le commentaire lié
        $commentaire = Commentaire::create([
            'id' => $interaction->id,
            'contenu' => $request->contenu,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commentaire ajouté',
            'data' => $commentaire->load('user')
        ], 201);
    }

    /**
     * Afficher un commentaire spécifique
     * GET /api/commentaires/{id}
     */
    public function show(string $id)
    {
        $commentaire = Commentaire::with(['user', 'publication'])->find($id);
        
        if (!$commentaire) {
            return response()->json([
                'success' => false,
                'message' => 'Commentaire non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $commentaire
        ]);
    }

    /**
     * Mettre à jour un commentaire
     * PUT/PATCH /api/commentaires/{id}
     */
    public function update(Request $request, string $id)
    {
        $commentaire = Commentaire::find($id);
        
        if (!$commentaire) {
            return response()->json([
                'success' => false,
                'message' => 'Commentaire non trouvé'
            ], 404);
        }

        // Seul l'auteur peut modifier
        if ($commentaire->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $commentaire->update(['contenu' => $request->contenu]);

        return response()->json([
            'success' => true,
            'message' => 'Commentaire mis à jour',
            'data' => $commentaire
        ]);
    }

    /**
     * Supprimer un commentaire
     * DELETE /api/commentaires/{id}
     */
    public function destroy(string $id)
    {
        $commentaire = Commentaire::find($id);
        
        if (!$commentaire) {
            return response()->json([
                'success' => false,
                'message' => 'Commentaire non trouvé'
            ], 404);
        }

        // Seul l'auteur ou modérateur peut supprimer
        if ($commentaire->user_id !== Auth::id()) {
            // Vérifier si modérateur du groupe
            $publication = $commentaire->publication;
            if ($publication->groupe && !$publication->groupe->estModerateur(Auth::user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non autorisé'
                ], 403);
            }
        }

        // Supprimer l'interaction parente (cascade supprimera le commentaire)
        $commentaire->interaction()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Commentaire supprimé'
        ]);
    }
}