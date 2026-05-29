<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Drive\{
    RenameModuleInAzure,
    MoveModuleInAzure,
    RenameElementInAzure,
    RenameDocumentInAzure,
    MoveDocumentInAzure,
    DeleteBlobsFromAzure,
};
use App\Models\{Module, ElementModule, Document, Annee};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DriveAdminController extends Controller
{
    // ─── Modules ──────────────────────────────────────────────────────────────

    // GET /api/admin/drive/modules
    public function indexModules(Request $request): JsonResponse
    {
        $modules = Module::with(['filiere:id,nom', 'anneeModel:id,label'])
            ->withCount('driveDocuments as documents_count')
            ->when($request->semestre, fn($q) => $q->where('semestre', $request->semestre))
            ->when($request->filiere_id, fn($q) => $q->where('filiere_id', $request->filiere_id))
            ->orderBy('semestre')
            ->orderBy('nom')
            ->get();

        return response()->json(['modules' => $modules]);
    }

    // POST /api/admin/drive/modules
    public function storeModule(Request $request): JsonResponse
    {
        $request->validate([
            'nom'        => 'required|string|max:255',
            'semestre'   => 'required|in:S1,S2,S3,S4,S5,S6',
            'filiere_id' => 'required|exists:filieres,id',
            'annee_id'   => 'required|exists:annees,id',
        ]);

        $module = Module::create([
            'nom'        => $request->nom,
            'slug'       => Str::slug($request->nom),
            'semestre'   => $request->semestre,
            'filiere_id' => $request->filiere_id,
            'annee_id'   => $request->annee_id,
            'is_active'  => true,
        ]);

        return response()->json(['module' => $module->fresh(['filiere:id,nom', 'anneeModel:id,label'])], 201);
    }

    // PUT /api/admin/drive/modules/{module}  — renomme nom et/ou semestre
    public function updateModule(Request $request, Module $module): JsonResponse
    {
        $request->validate([
            'nom'      => 'sometimes|string|max:255',
            'semestre' => 'sometimes|in:S1,S2,S3,S4,S5,S6',
        ]);

        $oldSlug = $module->slug;
        $newNom  = $request->nom ?? $module->nom;
        $newSlug = $request->nom ? Str::slug($request->nom) : $module->slug;

        $module->update([
            'nom'      => $newNom,
            'slug'     => $newSlug,
            'semestre' => $request->semestre ?? $module->semestre,
        ]);

        // Sync Azure seulement si le slug change (semestre absent du chemin Azure)
        if ($oldSlug !== $newSlug && $module->driveDocuments()->exists()) {
            RenameModuleInAzure::dispatch($module->id, $oldSlug, $newSlug);
        }

        return response()->json([
            'module'   => $module->fresh(['filiere:id,nom', 'anneeModel:id,label']),
            'syncing'  => ($oldSlug !== $newSlug),
        ]);
    }

    // PUT /api/admin/drive/modules/{module}/move  — change l'année (annee_id)
    public function moveModule(Request $request, Module $module): JsonResponse
    {
        $request->validate([
            'annee_id' => 'required|exists:annees,id',
        ]);

        $oldAnneeLabel = $module->anneeModel?->label;
        $module->update(['annee_id' => $request->annee_id]);
        $module->refresh();
        $newAnneeLabel = $module->anneeModel?->label;

        if ($oldAnneeLabel && $newAnneeLabel && $oldAnneeLabel !== $newAnneeLabel
            && $module->driveDocuments()->exists()) {
            MoveModuleInAzure::dispatch($module->id, $oldAnneeLabel, $newAnneeLabel);
        }

        return response()->json([
            'module'   => $module->fresh(['filiere:id,nom', 'anneeModel:id,label']),
            'syncing'  => ($oldAnneeLabel !== $newAnneeLabel),
        ]);
    }

    // DELETE /api/admin/drive/modules/{module}
    public function destroyModule(Request $request, Module $module): JsonResponse
    {
        $force     = $request->boolean('force', false);
        $docsCount = $module->driveDocuments()->count();

        if ($docsCount > 0 && !$force) {
            return response()->json([
                'warning'    => true,
                'message'    => "Ce module contient {$docsCount} document(s). Confirmer avec force=true.",
                'docs_count' => $docsCount,
            ], 409);
        }

        $elementIds = $module->elementModules()->pluck('id');

        // Collecter les chemins Azure avant suppression DB
        $azurePaths = Document::whereIn('element_module_id', $elementIds)
            ->whereNotNull('azure_path')
            ->pluck('azure_path')
            ->toArray();

        Document::whereIn('element_module_id', $elementIds)->forceDelete();
        $module->elementModules()->delete();
        $module->delete();

        if (!empty($azurePaths)) {
            DeleteBlobsFromAzure::dispatch($azurePaths, "module:{$module->nom}");
        }

        return response()->json([
            'success'      => true,
            'deleted_docs' => $docsCount,
            'syncing'      => !empty($azurePaths),
        ]);
    }

    // ─── Éléments ─────────────────────────────────────────────────────────────

    // POST /api/admin/drive/elements
    public function storeElement(Request $request): JsonResponse
    {
        $request->validate([
            'nom'       => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
        ]);

        $element = ElementModule::create([
            'nom'       => $request->nom,
            'slug'      => Str::slug($request->nom),
            'module_id' => $request->module_id,
        ]);

        return response()->json(['element' => $element], 201);
    }

    // PUT /api/admin/drive/elements/{element}
    public function updateElement(Request $request, ElementModule $element): JsonResponse
    {
        $request->validate(['nom' => 'required|string|max:255']);

        $oldSlug = $element->slug;
        $newSlug = Str::slug($request->nom);

        $element->update([
            'nom'  => $request->nom,
            'slug' => $newSlug,
        ]);

        if ($oldSlug !== $newSlug && $element->documents()->exists()) {
            RenameElementInAzure::dispatch($element->id, $oldSlug, $newSlug);
        }

        return response()->json([
            'element' => $element->fresh(),
            'syncing' => ($oldSlug !== $newSlug),
        ]);
    }

    // DELETE /api/admin/drive/elements/{element}
    public function destroyElement(ElementModule $element): JsonResponse
    {
        $docsCount = $element->documents()->count();

        $azurePaths = Document::where('element_module_id', $element->id)
            ->whereNotNull('azure_path')
            ->pluck('azure_path')
            ->toArray();

        Document::where('element_module_id', $element->id)->forceDelete();
        $element->delete();

        if (!empty($azurePaths)) {
            DeleteBlobsFromAzure::dispatch($azurePaths, "element:{$element->nom}");
        }

        return response()->json([
            'success'      => true,
            'deleted_docs' => $docsCount,
            'syncing'      => !empty($azurePaths),
        ]);
    }

    // ─── Documents ────────────────────────────────────────────────────────────

    // PUT /api/admin/drive/documents/{document}/rename
    public function renameDocument(Request $request, Document $document): JsonResponse
    {
        $request->validate(['titre' => 'required|string|max:255']);

        $document->update(['titre' => $request->titre]);

        if ($document->azure_path) {
            RenameDocumentInAzure::dispatch($document->id, $request->titre);
        }

        return response()->json([
            'document' => $document->fresh(),
            'syncing'  => (bool) $document->azure_path,
        ]);
    }

    // PUT /api/admin/drive/documents/{document}/move
    // Body: { element_module_id, typeDocument }
    public function moveDocument(Request $request, Document $document): JsonResponse
    {
        $request->validate([
            'element_module_id' => 'required|exists:element_modules,id',
            'typeDocument'      => 'required|string|max:100',
        ]);

        $targetElement = ElementModule::with('module.anneeModel', 'module.filiere')->findOrFail($request->element_module_id);
        $module        = $targetElement->module;
        $anneeLabel    = $module->anneeModel?->label ?? 'N/A';
        $filiereSlug   = strtoupper($module->filiere?->slug ?? $module->filiere?->nom ?? 'TC');

        // Nouveau dossier Azure : FILIERE/ANNEE/module-slug/element-slug/typeDocument
        $newAzureFolder = implode('/', [
            $filiereSlug,
            $anneeLabel,
            $module->slug ?? Str::slug($module->nom),
            $targetElement->slug ?? Str::slug($targetElement->nom),
            $request->typeDocument,
        ]);

        $document->update([
            'element_module_id' => $request->element_module_id,
            'typeDocument'      => $request->typeDocument,
        ]);

        if ($document->azure_path) {
            MoveDocumentInAzure::dispatch($document->id, $newAzureFolder, $request->typeDocument);
        }

        return response()->json([
            'document' => $document->fresh(),
            'syncing'  => (bool) $document->azure_path,
        ]);
    }

    // DELETE /api/admin/drive/documents/{document}
    public function destroyDocument(Document $document): JsonResponse
    {
        $azurePath = $document->azure_path;

        $document->forceDelete();

        if ($azurePath) {
            DeleteBlobsFromAzure::dispatch([$azurePath], "doc:{$document->titre}");
        }

        return response()->json([
            'success' => true,
            'syncing' => (bool) $azurePath,
        ]);
    }

    // ─── Sync status ──────────────────────────────────────────────────────────

    // GET /api/admin/drive/sync-status
    public function syncStatus(): JsonResponse
    {
        $pending = DB::table('jobs')
            ->where('queue', 'default')
            ->count();

        $failed = DB::table('failed_jobs')->count();

        return response()->json([
            'pending_jobs' => $pending,
            'failed_jobs'  => $failed,
            'is_syncing'   => $pending > 0,
        ]);
    }
}
