<?php

namespace App\Http\Controllers;

use App\Models\DocumentReview;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DocumentReviewController extends Controller
{
    /**
     * Afficher la liste des validations
     * GET /api/document-reviews
     */
    public function index(Request $request)
    {
        $query = DocumentReview::with(['document', 'moderateur']);
        
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
     * Créer une validation de document
     * POST /api/document-reviews
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_id' => 'required|exists:documents,id',
            'statut' => 'required|in:EnAttente,Valide,Rejete',
            'motif' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $review = DocumentReview::create([
            'document_id' => $request->document_id,
            'moderateur_id' => Auth::id(),
            'statut' => $request->statut,
            'reviewedAt' => now(),
            'motif' => $request->motif,
        ]);

        // Mettre à jour le statut du document
        $document = Document::find($request->document_id);
        $document->update(['statutValidation' => $request->statut]);
        
        if ($request->statut === 'Valide') {
            $document->update(['publishedAt' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Validation enregistrée',
            'data' => $review
        ], 201);
    }

    /**
     * Afficher une validation spécifique
     * GET /api/document-reviews/{id}
     */
    public function show(string $id)
    {
        $review = DocumentReview::with(['document', 'moderateur'])->find($id);
        
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
     * PUT/PATCH /api/document-reviews/{id}
     */
    public function update(Request $request, string $id)
    {
        $review = DocumentReview::find($id);
        
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

        // Mettre à jour le document associé
        $review->document->update(['statutValidation' => $request->statut]);

        return response()->json([
            'success' => true,
            'message' => 'Validation mise à jour',
            'data' => $review
        ]);
    }

    /**
     * Supprimer une validation
     * DELETE /api/document-reviews/{id}
     */
    public function destroy(string $id)
    {
        $review = DocumentReview::find($id);
        
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

    /**
     * Valider un document
     * POST /api/document-reviews/{id}/validate
     */
    public function valider(Request $request, string $id)
    {
        $review = DocumentReview::where('document_id', $id)->first();
        
        if (!$review) {
            $review = new DocumentReview();
            $review->document_id = $id;
        }

        $review->update([
            'moderateur_id' => Auth::id(),
            'statut' => 'Valide',
            'reviewedAt' => now(),
            'motif' => $request->motif,
        ]);

        $review->document->update([
            'statutValidation' => 'Valide',
            'publishedAt' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document validé avec succès'
        ]);
    }

    /**
     * Rejeter un document
     * POST /api/document-reviews/{id}/reject
     */
    public function rejeter(Request $request, string $id)
    {
        $review = DocumentReview::where('document_id', $id)->first();
        
        if (!$review) {
            $review = new DocumentReview();
            $review->document_id = $id;
        }

        $review->update([
            'moderateur_id' => Auth::id(),
            'statut' => 'Rejete',
            'reviewedAt' => now(),
            'motif' => $request->motif,
        ]);

        $review->document->update(['statutValidation' => 'Rejete']);

        return response()->json([
            'success' => true,
            'message' => 'Document rejeté',
            'motif' => $request->motif
        ]);
    }
}