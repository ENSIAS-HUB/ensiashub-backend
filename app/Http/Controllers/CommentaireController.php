<?php

namespace App\Http\Controllers;

use App\Models\Commentaire;
use App\Models\Interaction;
use App\Models\Publication;
use App\Http\Resources\CommentResource;
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
        $query = Interaction::with(['user'])
            ->where('interactions.type', 'commentaire')
            ->join('commentaires', 'interactions.id', '=', 'commentaires.id')
            ->select('interactions.*', 'commentaires.contenu');

        if ($request->has('publication_id')) {
            $query->where('interactions.publication_id', $request->publication_id);
        }

        $commentaires = $query->orderBy('interactions.created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data'    => CommentResource::collection($commentaires),
        ]);
    }

    /**
     * Créer un nouveau commentaire
     * POST /api/commentaires
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'        => 'sometimes|required_without:contenu|string',
            'contenu'        => 'sometimes|required_without:content|string',
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

        // Créer l'interaction parente
        $interaction = Interaction::create([
            'user_id'        => Auth::id(),
            'publication_id' => $request->publication_id,
            'type'           => 'commentaire',
        ]);

        // Créer le commentaire lié
        Commentaire::create([
            'id'     => $interaction->id,
            'contenu'=> $request->input('content') ?? $request->input('contenu'),
        ]);

        // Reload from join to get contenu + user
        $result = Interaction::with(['user'])
            ->where('interactions.id', $interaction->id)
            ->join('commentaires', 'interactions.id', '=', 'commentaires.id')
            ->select('interactions.*', 'commentaires.contenu')
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Commentaire ajouté',
            'data'    => (new CommentResource($result))->toArray($request),
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