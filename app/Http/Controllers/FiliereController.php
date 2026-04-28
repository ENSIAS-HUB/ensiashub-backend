<?php

namespace App\Http\Controllers;

use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FiliereController extends Controller
{
    /**
     * Afficher la liste des filières
     * GET /api/filieres
     */
    public function index(Request $request)
    {
        $query = Filiere::withCount('modules');
        
        if ($request->has('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }
        
        $filieres = $query->orderBy('nom', 'asc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $filieres
        ]);
    }

    /**
     * Créer une nouvelle filière
     * POST /api/filieres
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:filieres',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $filiere = Filiere::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Filière créée avec succès',
            'data' => $filiere
        ], 201);
    }

    /**
     * Afficher une filière spécifique
     * GET /api/filieres/{id}
     */
    public function show(string $id)
    {
        $filiere = Filiere::with(['modules.documents'])->find($id);
        
        if (!$filiere) {
            return response()->json([
                'success' => false,
                'message' => 'Filière non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $filiere
        ]);
    }

    /**
     * Mettre à jour une filière
     * PUT/PATCH /api/filieres/{id}
     */
    public function update(Request $request, string $id)
    {
        $filiere = Filiere::find($id);
        
        if (!$filiere) {
            return response()->json([
                'success' => false,
                'message' => 'Filière non trouvée'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255|unique:filieres,nom,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $filiere->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Filière mise à jour',
            'data' => $filiere
        ]);
    }

    /**
     * Supprimer une filière
     * DELETE /api/filieres/{id}
     */
    public function destroy(string $id)
    {
        $filiere = Filiere::find($id);
        
        if (!$filiere) {
            return response()->json([
                'success' => false,
                'message' => 'Filière non trouvée'
            ], 404);
        }

        // Vérifier si la filière contient des modules
        if ($filiere->modules()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette filière car elle contient des modules',
                'modules_count' => $filiere->modules()->count()
            ], 400);
        }

        $filiere->delete();

        return response()->json([
            'success' => true,
            'message' => 'Filière supprimée avec succès'
        ]);
    }

    /**
     * Obtenir les modules d'une filière
     * GET /api/filieres/{id}/modules
     */
    public function getModules(string $id)
    {
        $filiere = Filiere::find($id);
        
        if (!$filiere) {
            return response()->json([
                'success' => false,
                'message' => 'Filière non trouvée'
            ], 404);
        }

        $modules = $filiere->modules()->withCount('documents')->get();

        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }
}