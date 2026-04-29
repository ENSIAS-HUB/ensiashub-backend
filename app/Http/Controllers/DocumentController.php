<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ModulePedagogique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Afficher la liste des documents
     * GET /api/documents
     */
    public function index(Request $request)
    {
        $query = Document::with(['user', 'module.filiere']);
        
        if ($request->has('module_id')) {
            $query->where('module_pedagogique_id', $request->module_id);
        }
        
        if ($request->has('type')) {
            $query->where('typeDocument', $request->type);
        }
        
        if ($request->has('statut')) {
            $query->where('statutValidation', $request->statut);
        }
        
        if ($request->has('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%')
                  ->orWhere('nom', 'like', '%' . $request->search . '%');
        }
        
        $documents = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Uploader un nouveau document
     * POST /api/documents
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titre' => 'required|string|max:255',
            'module_pedagogique_id' => 'required|exists:modules,id',
            'typeDocument' => 'required|string|in:Cours,TD,Examen,Autre',
            'fichier' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload du fichier
        $file = $request->file('fichier');
        $fileName = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents', $fileName, 'public');

        $document = Document::create([
            'titre' => $request->titre,
            'nom' => $file->getClientOriginalName(),
            'format' => $file->getClientOriginalExtension(),
            'urlStockage' => $path,
            'typeDocument' => $request->typeDocument,
            'uploader_id' => Auth::id(),
            'module_pedagogique_id' => $request->module_pedagogique_id,
            'statutValidation' => 'EnAttente',
        ]);

        // Créer automatiquement une validation en attente
        $document->validation()->create([
            'moderateur_id' => Auth::id(),
            'statut' => 'EnAttente',
            'reviewedAt' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploadé avec succès',
            'data' => $document->load(['user', 'module'])
        ], 201);
    }

    /**
     * Afficher un document spécifique
     * GET /api/documents/{id}
     */
    public function show(string $id)
    {
        $document = Document::with(['user', 'module.filiere', 'validation'])->find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Incrémenter le compteur de téléchargements
        $document->increment('downloads_count');

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Mettre à jour un document
     * PUT/PATCH /api/documents/{id}
     */
    public function update(Request $request, string $id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Seul l'uploader peut modifier
        if ($document->uploader_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'titre' => 'sometimes|string|max:255',
            'typeDocument' => 'sometimes|string|in:Cours,TD,Examen,Autre',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $document->update($request->only(['titre', 'typeDocument']));

        return response()->json([
            'success' => true,
            'message' => 'Document mis à jour',
            'data' => $document
        ]);
    }

    /**
     * Supprimer un document
     * DELETE /api/documents/{id}
     */
    public function destroy(string $id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Seul l'uploader ou modérateur peut supprimer
        if ($document->uploader_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Supprimer le fichier physique
        if ($document->urlStockage && Storage::disk('public')->exists($document->urlStockage)) {
            Storage::disk('public')->delete($document->urlStockage);
        }

        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ]);
    }

    /**
     * Télécharger un document
     * GET /api/documents/{id}/download
     */
    public function download(string $id)
    {
        $document = Document::find($id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document non trouvé'
            ], 404);
        }

        // Vérifier que le document est validé
        if ($document->statutValidation !== 'Valide') {
            return response()->json([
                'success' => false,
                'message' => 'Ce document n\'est pas encore validé'
            ], 403);
        }

        // Incrémenter le compteur
        $document->increment('downloads_count');

        $filePath = storage_path('app/public/' . $document->urlStockage);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        }

        return response()->download($filePath, $document->nom);
    }
}