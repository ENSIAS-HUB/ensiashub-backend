<?php

namespace App\Http\Controllers;

use App\Models\PublicationReview;
use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PublicationReviewController extends Controller
{
    /**
     * Afficher la liste des validations
     * GET /api/publication-reviews
     */
    public function index(Request $request)
    {
        $query = PublicationReview::with(['publication', 'moderateur']);
        
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        $reviews = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Créer une validation de publication
     * POST /api/publication-reviews
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'publication_id' => 'required|exists:publications,id',
            'statut' => 'required|in:EnAttente,Valide,Rejete',
            'motif' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review = PublicationReview::create([
            'publication_id' => $request->publication_id,
            'moderateur_id' => Auth::id(),
            'statut' => $request->statut,
            'reviewedAt' => now(),
            'motif' => $request->motif,
        ]);

        // Mettre à jour le statut de la publication
        $publication = Publication::find($request->publication_id);
        $publication->update(['statutValidation' => $request->statut]);
        
        if ($request->statut === 'Valide') {
            $publication->update(['publishedAt' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation enregistrée',
            'data' => $review
        ], 201);
    }

    /**
     * Afficher une validation spécifique
     * GET /api/publication-reviews/{id}
     */
    public function show(string $id)
    {
        $review = PublicationReview::with(['publication', 'moderateur'])->find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Validation non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    /**
     * Mettre à jour une validation
     * PUT/PATCH /api/publication-reviews/{id}
     */
    public function update(Request $request, string $id)
    {
        $review = PublicationReview::find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Validation non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'statut' => 'sometimes|in:EnAttente,Valide,Rejete',
            'motif' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update([
            'statut' => $request->statut,
            'reviewedAt' => now(),
            'motif' => $request->motif,
        ]);

        // Mettre à jour la publication associée
        $review->publication->update(['statutValidation' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Validation mise à jour',
            'data' => $review
        ]);
    }

    /**
     * Supprimer une validation
     * DELETE /api/publication-reviews/{id}
     */
    public function destroy(string $id)
    {
        $review = PublicationReview::find($id);
        
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Validation non trouvée'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Validation supprimée'
        ]);
    }
}