<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\DriveUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        private DriveUploadService $uploadService
    ) {}

    // GET /api/drive/documents
    public function index(Request $request): JsonResponse
    {
        $query = Document::with([
            'uploader:id,nom,prenom,photoProfil',
            'filiere:id,nom,code,slug',
            'module:id,nom,semestre,filiere_id',
        ])->withCount('allComments');

        if ($request->filled('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }
        if ($request->filled('module_id')) {
            $query->where('module_pedagogique_id', $request->module_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('type')) {
            $query->where('typeDocument', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }

        // Only show Azure-uploaded documents on this endpoint
        $query->whereNotNull('azure_path');

        $userId    = $request->user()->id;
        $documents = $query->latest()->paginate(20);

        $documents->getCollection()->transform(function ($doc) use ($userId) {
            $doc->is_saved = $doc->isSavedBy($userId);
            return $doc;
        });

        return response()->json($documents);
    }

    // POST /api/drive/documents
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file'        => 'required|file|max:102400',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'filiere_id'  => 'nullable|exists:filieres,id',
            'module_id'   => 'nullable|exists:modules,id',
            'type'        => 'nullable|in:cours,td,tp,examen,corrige,resume,projet,autre',
            'semester'    => 'nullable|string|in:S1,S2,S3,S4,S5,S6',
            'year'        => 'nullable|integer|min:1|max:5',
        ]);

        try {
            $document = $this->uploadService->upload(
                $request->file('file'),
                $request->only([
                    'title', 'description', 'filiere_id', 'module_id',
                    'type', 'semester', 'year',
                    'filiere_slug', 'module_slug',
                ]),
                $request->user()->id
            );

            $document->load([
                'uploader:id,nom,prenom,photoProfil',
                'filiere:id,nom,code,slug',
                'module:id,nom,semestre,filiere_id',
            ]);

            return response()->json(['document' => $document], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => "Erreur lors de l'upload"], 500);
        }
    }

    // GET /api/drive/documents/{document}
    public function show(Request $request, Document $document): JsonResponse
    {
        $document->increment('views_count');
        $document->load([
            'uploader:id,nom,prenom,photoProfil',
            'filiere:id,nom,code,slug',
            'module:id,nom,semestre,filiere_id',
        ]);
        $document->is_saved = $document->isSavedBy($request->user()->id);

        return response()->json(['document' => $document]);
    }

    // GET /api/drive/documents/{document}/download
    public function download(Document $document): JsonResponse
    {
        $document->increment('downloads_count');

        return response()->json(['url' => $document->getDownloadUrl()]);
    }

    // DELETE /api/drive/documents/{document}
    public function destroy(Request $request, Document $document): JsonResponse
    {
        $user = $request->user();

        if ($document->uploader_id !== $user->id && $user->role !== 'superAdmin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $document->forceDelete();

        return response()->json(['success' => true]);
    }
}
