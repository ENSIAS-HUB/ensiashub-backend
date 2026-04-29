<?php

namespace App\Http\Controllers;

use App\Models\Interaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InteractionController extends Controller
{
    /**
     * Afficher la liste des interactions d'une publication
     * GET /api/publications/{publication_id}/interactions
     */
    public function index(Request $request)
    {
        $query = Interaction::with(['user']);
        
        if ($request->has('publication_id')) {
            $query->where('publication_id', $request->publication_id);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $interactions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $interactions
        ]);
    }

    /**
     * Afficher une interaction spécifique
     * GET /api/interactions/{id}
     */
    public function show(string $id)
    {
        $interaction = Interaction::with(['user', 'publication'])->find($id);
        
        if (!$interaction) {
            return response()->json([
                'success' => false,
                'message' => 'Interaction non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $interaction
        ]);
    }

    /**
     * Supprimer une interaction
     * DELETE /api/interactions/{id}
     */
    public function destroy(string $id)
    {
        $interaction = Interaction::find($id);
        
        if (!$interaction) {
            return response()->json([
                'success' => false,
                'message' => 'Interaction non trouvée'
            ], 404);
        }

        // Seul l'auteur ou modérateur peut supprimer
        if ($interaction->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $interaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interaction supprimée'
        ]);
    }
}