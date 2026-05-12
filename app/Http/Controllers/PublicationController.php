<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\Group;
use App\Http\Resources\PublicationResource;
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
        $groupId = $request->input('group_id') ?? $request->input('groupe_id');

        $paginator = Publication::with(['user', 'groupe'])
            ->withCount([
                'reactions',
                'commentaires as comments_count',
            ])
            ->when(
                auth()->check(),
                fn ($q) => $q->withExists([
                    'reactions as user_reacted' => fn ($q) => $q->where('user_id', auth()->id()),
                ])
            )
            ->when($groupId, fn ($q) => $q->where('groupe_id', $groupId))
            ->when($request->has('statut'), fn ($q) => $q->where('statutValidation', $request->statut))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Return raw paginator shape (current_page, last_page, data) that the frontend expects
        return response()->json(
            $paginator->through(fn ($pub) => (new PublicationResource($pub))->toArray($request))
        );
    }

    /**
     * Créer une nouvelle publication
     * POST /api/publications
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'   => 'sometimes|required_without:contenu|string',
            'contenu'   => 'sometimes|required_without:content|string',
            'media_url' => 'nullable|string',
            'typeMedia' => 'nullable|string',
            'group_id'  => 'nullable|exists:groups,id',
            'groupe_id' => 'nullable|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $publication = Publication::create([
            'contenu'          => $request->input('content') ?? $request->input('contenu'),
            'typeMedia'        => $request->input('media_url') ?? $request->input('typeMedia'),
            'user_id'          => Auth::id(),
            'groupe_id'        => $request->input('group_id') ?? $request->input('groupe_id'),
            'statutValidation' => 'EnAttente',
        ]);

        $publication->load(['user', 'groupe']);

        return response()->json([
            'success' => true,
            'message' => 'Publication créée avec succès',
            'data'    => (new PublicationResource($publication))->toArray($request),
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