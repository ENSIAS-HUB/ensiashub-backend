<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PublicationController extends Controller
{
    /**
     * Afficher la liste des publications
     * GET /api/publications
     */
    public function index(Request $request)
    {
        $query = Publication::with(['user', 'groupe']);
        
        if ($request->has('groupe_id')) {
            $query->where('groupe_id', $request->groupe_id);
        }
        
        if ($request->has('statut')) {
            $query->where('statutValidation', $request->statut);
        }
        
        $publications = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $publications
        ]);
    }

    /**
     * Créer une nouvelle publication
     * POST /api/publications
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
            'typeMedia' => 'nullable|string',
            'groupe_id' => 'nullable|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $publication = Publication::create([
            'contenu' => $request->contenu,
            'typeMedia' => $request->typeMedia,
            'user_id' => Auth::id(),
            'groupe_id' => $request->groupe_id,
            'statutValidation' => 'EnAttente',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Publication créée avec succès',
            'data' => $publication
        ], 201);
    }

    /**
     * Afficher une publication spécifique
     * GET /api/publications/{id}
     */
    public function show(string $id)
    {
        $publication = Publication::with(['user', 'groupe', 'commentaires', 'reactions'])->find($id);
        
        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $publication
        ]);
    }

    /**
     * Mettre à jour une publication
     * PUT/PATCH /api/publications/{id}
     */
    public function update(Request $request, string $id)
    {
        $publication = Publication::find($id);
        
        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication non trouvée'
            ], 404);
        }

        // Seul l'auteur peut modifier
        if ($publication->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'contenu' => 'sometimes|string',
            'typeMedia' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $publication->update($request->only(['contenu', 'typeMedia']));

        return response()->json([
            'success' => true,
            'message' => 'Publication mise à jour',
            'data' => $publication
        ]);
    }

    /**
     * Supprimer une publication
     * DELETE /api/publications/{id}
     */
    public function destroy(string $id)
    {
        $publication = Publication::find($id);
        
        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication non trouvée'
            ], 404);
        }

        // Seul l'auteur ou modérateur peut supprimer
        $user = Auth::user();
        $isModerateur = $publication->groupe && $publication->groupe->estModerateur($user);
        
        if ($publication->user_id !== $user->id && !$isModerateur) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $publication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Publication supprimée'
        ]);
    }

    /**
     * Publier une publication (la rendre visible)
     * POST /api/publications/{id}/publish
     */
    public function publier(string $id)
    {
        $publication = Publication::find($id);
        
        if (!$publication) {
            return response()->json([
                'success' => false,
                'message' => 'Publication non trouvée'
            ], 404);
        }

        $publication->update([
            'statutValidation' => 'Valide',
            'publishedAt' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Publication publiée'
        ]);
    }
}