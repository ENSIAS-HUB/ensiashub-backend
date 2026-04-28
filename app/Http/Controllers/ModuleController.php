<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Filiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModuleController extends Controller
{
    /**
     * Afficher la liste des modules
     * GET /api/modules
     */
    public function index(Request $request)
    {
        $query = Module::with(['filiere']);
        
        if ($request->has('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }
        
        if ($request->has('semestre')) {
            $query->where('semestre', $request->semestre);
        }
        
        if ($request->has('annee')) {
            $query->where('annee', $request->annee);
        }
        
        if ($request->has('search')) {
            $query->where('nom', 'like', '%' . $request->search . '%');
        }
        
        $modules = $query->orderBy('annee')->orderBy('semestre')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }

    /**
     * Créer un nouveau module
     * POST /api/modules
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filiere_id' => 'required|exists:filieres,id',
            'nom' => 'required|string|max:255',
            'semestre' => 'required|string|in:S1,S2,S3,S4,S5,S6',
            'annee' => 'required|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si le module existe déjà dans cette filière
        $existant = Module::where('filiere_id', $request->filiere_id)
                                     ->where('nom', $request->nom)
                                     ->first();
        
        if ($existant) {
            return response()->json([
                'success' => false,
                'message' => 'Ce module existe déjà dans cette filière'
            ], 400);
        }

        $module = Module::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Module créé avec succès',
            'data' => $module->load('filiere')
        ], 201);
    }

    /**
     * Afficher un module spécifique
     * GET /api/modules/{id}
     */
    public function show(string $id)
    {
        $module = Module::with(['filiere', 'documents.user', 'documents.validation'])->find($id);
        
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $module
        ]);
    }

    /**
     * Mettre à jour un module
     * PUT/PATCH /api/modules/{id}
     */
    public function update(Request $request, string $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module non trouvé'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'semestre' => 'sometimes|string|in:S1,S2,S3,S4,S5,S6',
            'annee' => 'sometimes|integer|in:1,2,3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $module->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Module mis à jour',
            'data' => $module
        ]);
    }

    /**
     * Supprimer un module
     * DELETE /api/modules/{id}
     */
    public function destroy(string $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module non trouvé'
            ], 404);
        }

        // Vérifier si le module contient des documents
        if ($module->documents()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce module car il contient des documents',
                'documents_count' => $module->documents()->count()
            ], 400);
        }

        $module->delete();

        return response()->json([
            'success' => true,
            'message' => 'Module supprimé avec succès'
        ]);
    }

    /**
     * Obtenir les documents d'un module
     * GET /api/modules/{id}/documents
     */
    public function getDocuments(string $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module non trouvé'
            ], 404);
        }

        $documents = $module->documents()
                            ->where('statutValidation', 'Valide')
                            ->with('user')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Obtenir les statistiques du module
     * GET /api/modules/{id}/stats
     */
    public function getStats(string $id)
    {
        $module = Module::find($id);
        
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module non trouvé'
            ], 404);
        }

        $stats = [
            'total_documents' => $module->documents()->count(),
            'documents_valides' => $module->documents()->where('statutValidation', 'Valide')->count(),
            'documents_en_attente' => $module->documents()->where('statutValidation', 'EnAttente')->count(),
            'documents_par_type' => [
                'Cours' => $module->documents()->where('typeDocument', 'Cours')->count(),
                'TD' => $module->documents()->where('typeDocument', 'TD')->count(),
                'Examen' => $module->documents()->where('typeDocument', 'Examen')->count(),
                'Autre' => $module->documents()->where('typeDocument', 'Autre')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}