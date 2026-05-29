<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Annee;
use App\Models\Document;
use App\Models\ElementModule;
use App\Models\Filiere;
use App\Models\Module;
use App\Services\DriveQueryFilter;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveController extends Controller
{
    public function __construct(private DriveQueryFilter $filter) {}

    // ── GET /api/me/drive-access ──────────────────────────────────────────────
    public function myAccess(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($this->filter->isFullAccess($user)) {
            $filieres = Filiere::where('is_active', true)
                ->orderBy('nom')
                ->get(['id', 'nom', 'slug', 'badge', 'is_tronc_commun']);

            return response()->json([
                'full_access' => true,
                'filieres'    => $filieres,
            ]);
        }

        if (!$this->filter->hasCompleteProfile($user)) {
            return response()->json([
                'error'   => 'PROFILE_INCOMPLETE',
                'message' => 'Veuillez compléter votre profil (filière et année).',
            ], 403);
        }

        $filiere = $this->filter->resolveUserFiliere($user);
        $annee   = $this->filter->resolveUserAnnee($user);

        return response()->json([
            'full_access' => false,
            'filiere'     => $filiere ? ['id' => $filiere->id, 'nom' => $filiere->nom, 'badge' => $filiere->badge] : null,
            'annee'       => $user->annee,
        ]);
    }

    // ── GET /api/drive/filieres — vue admin (sidebar) ─────────────────────────
    // N'affiche que les filières qui possèdent au moins un document
    // synchronisé depuis Azure (azure_path non nul).
    public function filieres(): JsonResponse
    {
        $filieres = Filiere::where('is_active', true)
            ->whereHas('modules.elementModules.documents', function ($q) {
                $q->whereNotNull('azure_path');
            })
            ->withCount(['modules' => function ($q) {
                $q->whereHas('elementModules.documents', function ($q2) {
                    $q2->whereNotNull('azure_path');
                });
            }])
            ->orderByRaw('is_tronc_commun DESC')
            ->orderBy('nom')
            ->get()
            ->map(fn($f) => [
                'id'              => $f->id,
                'nom'             => $f->nom,
                'slug'            => $f->slug,
                'badge'           => $f->badge,
                'is_tronc_commun' => (bool) $f->is_tronc_commun,
                'modules_count'   => $f->modules_count,
            ]);

        return response()->json(['filieres' => $filieres]);
    }

    // ── GET /api/drive/mes-modules — vue étudiant ─────────────────────────────
    public function mesModules(Request $request): JsonResponse
    {
        $user = $request->user();

        // Bloquer cuisinier / buvette
        $blocked = ['cuisinier', 'buvette'];
        foreach ($blocked as $r) {
            if ($user->hasRole($r)) {
                return response()->json(['error' => 'Accès non autorisé au Drive.'], 403);
            }
        }

        // Résoudre la filière depuis le profil
        // Mapping rétrocompatibilité : anciens noms → nouveaux noms officiels
        $legacyNames = ['2AI' => '2IA', '2SCL' => '2CSL'];
        $userFiliere = $user->filiere ?? '';
        $userFiliere = $legacyNames[$userFiliere] ?? $userFiliere;

        $filiere = Filiere::where('nom', $userFiliere)
                          ->orWhere('slug', Str::slug($userFiliere))
                          ->first();

        if (! $filiere) {
            return response()->json([
                'error'        => "Filière '{$userFiliere}' non configurée.",
                'user_filiere' => $userFiliere,
            ], 422);
        }

        // Résoudre l'année depuis le profil
        $userAnnee = $user->annee ?? '1A';
        $annee     = Annee::where('label', $userAnnee)->first();

        if (! $annee) {
            $niveau = (int) preg_replace('/\D/', '', $userAnnee);
            $annee  = $niveau ? Annee::where('niveau', $niveau)->first() : null;
        }

        if (! $annee) {
            return response()->json([
                'error'      => "Année '{$userAnnee}' introuvable.",
                'user_annee' => $userAnnee,
            ], 422);
        }

        // 1A → filière + toutes les filières Tronc Commun ; 2A/3A → filière uniquement
        if ($annee->niveau === 1) {
            $troncIds   = Filiere::where('is_tronc_commun', true)->where('is_active', true)->pluck('id')->toArray();
            $filiereIds = array_unique(array_merge([$filiere->id], $troncIds));

            $modules = Module::whereIn('filiere_id', $filiereIds)
                             ->where('annee_id', $annee->id)
                             ->where('is_active', true)
                             ->withCount(['driveDocuments as documents_count'])
                             ->with('filiere:id,nom,slug,badge,is_tronc_commun')
                             ->orderByRaw('filiere_id IN (SELECT id FROM filieres WHERE is_tronc_commun = true) ASC')
                             ->orderBy('nom')
                             ->get();
        } else {
            $modules = Module::where('filiere_id', $filiere->id)
                             ->where('annee_id', $annee->id)
                             ->where('is_active', true)
                             ->withCount(['driveDocuments as documents_count'])
                             ->with('filiere:id,nom,slug,badge')
                             ->orderBy('nom')
                             ->get();
        }

        return response()->json([
            'filiere' => ['nom' => $filiere->nom, 'badge' => $filiere->badge],
            'annee'   => $annee->label,
            'modules' => $modules,
        ]);
    }

    // ── GET /api/drive/mes-arborescence — vue étudiant (arborescence) ─────────
    public function mesArborescence(Request $request): JsonResponse
    {
        $user = $request->user();

        $blocked = ['cuisinier', 'buvette'];
        foreach ($blocked as $r) {
            if ($user->hasRole($r)) {
                return response()->json(['error' => 'Accès non autorisé au Drive.'], 403);
            }
        }

        $legacyNames = ['2AI' => '2IA', '2SCL' => '2CSL'];
        $userFiliere = $user->filiere ?? '';
        $userFiliere = $legacyNames[$userFiliere] ?? $userFiliere;

        $filiere = Filiere::where('nom', $userFiliere)
                          ->orWhere('slug', Str::slug($userFiliere))
                          ->first();

        if (! $filiere) {
            return response()->json([
                'error'        => "Filière '{$userFiliere}' non configurée.",
                'user_filiere' => $userFiliere,
            ], 422);
        }

        $userAnnee = $user->annee ?? '1A';
        $annee     = Annee::where('label', $userAnnee)->first();

        if (! $annee) {
            $niveau = (int) preg_replace('/\D/', '', $userAnnee);
            $annee  = $niveau ? Annee::where('niveau', $niveau)->first() : null;
        }

        if (! $annee) {
            return response()->json([
                'error'      => "Année '{$userAnnee}' introuvable.",
                'user_annee' => $userAnnee,
            ], 422);
        }

        if ($annee->niveau === 1) {
            $troncIds   = Filiere::where('is_tronc_commun', true)->where('is_active', true)->pluck('id')->toArray();
            $filiereIds = array_unique(array_merge([$filiere->id], $troncIds));
        } else {
            $filiereIds = [$filiere->id];
        }

        return response()->json([
            'annee'        => $annee->label,
            // Pas de mention "Tronc Commun" ni du nom de filière dans la réponse étudiant
            'arborescence' => $this->buildArborescence($filiereIds, $annee),
        ]);
    }

    // ── GET /api/drive/filieres/{filiere}/arborescence — vue admin ────────────
    public function arborescence(Request $request, Filiere $filiere): JsonResponse
    {
        $anneeLabel = $request->query('annee', '1A');
        $annee      = Annee::where('label', $anneeLabel)->firstOrFail();

        if ($annee->niveau === 1) {
            $troncIds   = Filiere::where('is_tronc_commun', true)->where('is_active', true)->pluck('id')->toArray();
            $filiereIds = array_unique(array_merge([$filiere->id], $troncIds));
        } else {
            $filiereIds = [$filiere->id];
        }

        return response()->json([
            'filiere'      => $filiere->nom,
            'annee'        => $anneeLabel,
            'arborescence' => $this->buildArborescence($filiereIds, $annee),
        ]);
    }

    private function buildArborescence(array $filiereIds, Annee $annee): array
    {
        $modules = Module::whereIn('filiere_id', $filiereIds)
            ->where('annee_id', $annee->id)
            ->where('is_active', true)
            ->with([
                'elementModules' => fn($q) => $q->withCount('documents'),
                'elementModules.documents' => fn($q) => $q
                    ->select('id', 'element_module_id', 'titre', 'typeDocument',
                             'azure_url', 'extension', 'taille', 'nom', 'created_at')
                    ->orderBy('typeDocument')
                    ->orderBy('titre'),
            ])
            ->withCount('driveDocuments as documents_count')
            ->orderBy('semestre')
            ->orderBy('nom')
            ->get();

        return $modules
            ->groupBy('semestre')
            ->map(function ($modulesGroup, $semestre) {
                return [
                    'semestre' => $semestre ?? 'S?',
                    'modules'  => $modulesGroup->map(function (Module $module) {
                        return [
                            'id'              => $module->id,
                            'nom'             => $module->nom,
                            'documents_count' => $module->documents_count,
                            'elements'        => $module->elementModules->map(function (ElementModule $el) {
                                $parType = $el->documents
                                    ->groupBy('typeDocument')
                                    ->map(fn($docs, $type) => [
                                        'type'      => $type,
                                        'count'     => $docs->count(),
                                        'documents' => $docs->map(fn($d) => [
                                            'id'           => $d->id,
                                            'titre'        => $d->titre,
                                            'type'         => $d->typeDocument,
                                            'extension'    => $d->extension,
                                            'taille_bytes' => $d->taille,
                                            'azure_url'    => $d->azure_url,
                                            'nom_original' => $d->nom,
                                            'created_at'   => $d->created_at,
                                        ])->values(),
                                    ])
                                    ->values();

                                return [
                                    'id'              => $el->id,
                                    'nom'             => $el->nom,
                                    'documents_count' => $el->documents_count,
                                    'types'           => $parType,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })
            ->sortBy('semestre')
            ->values()
            ->toArray();
    }

    // ── GET /api/drive/filieres/{filiere}/modules — vue admin ─────────────────
    public function modulesByFiliere(Request $request, Filiere $filiere): JsonResponse
    {
        $anneeLabel = $request->query('annee', '1A');
        $annee      = Annee::where('label', $anneeLabel)->first();

        $query = Module::where('filiere_id', $filiere->id)
                       ->where('is_active', true)
                       ->withCount(['driveDocuments as documents_count'])
                       ->with('filiere:id,nom,slug,badge');

        if ($annee) {
            $query->where('annee_id', $annee->id);
        }

        $modules = $query->orderBy('nom')->get();

        return response()->json([
            'filiere' => ['nom' => $filiere->nom, 'badge' => $filiere->badge],
            'annee'   => $anneeLabel,
            'modules' => $modules,
        ]);
    }

    // ── GET /api/drive/modules/{module}/elements ──────────────────────────────
    public function elementsOfModule(Request $request, Module $module): JsonResponse
    {
        $userId = $request->user()->id;

        $elements = $module->elementModules()
            ->withCount('documents')
            ->with([
                'documents' => fn($q) => $q
                    ->with('uploader:id,nom,prenom,photoProfil')
                    ->orderBy('typeDocument')
                    ->orderBy('titre'),
            ])
            ->orderBy('nom')
            ->get()
            ->map(function (ElementModule $el) use ($userId) {
                $types = $el->documents
                    ->groupBy('typeDocument')
                    ->map(function ($docs, $type) use ($userId) {
                        return [
                            'type'      => $type,
                            'count'     => $docs->count(),
                            'documents' => $docs->map(function ($d) use ($userId) {
                                $d->is_saved = $d->isSavedBy($userId);
                                return [
                                    'id'              => $d->id,
                                    'titre'           => $d->titre,
                                    'type'            => $d->typeDocument,
                                    'extension'       => $d->extension,
                                    'taille_formatee' => $d->size_formatted,
                                    'telechargements' => $d->downloads_count,
                                    'vues'            => $d->views_count,
                                    'is_saved'        => $d->is_saved,
                                    'azure_url'       => $d->azure_url,
                                    'created_at'      => $d->created_at,
                                    'uploader'        => $d->uploader ? [
                                        'id'         => $d->uploader->id,
                                        'nom'        => $d->uploader->nom,
                                        'prenom'     => $d->uploader->prenom,
                                        'avatar_url' => $d->uploader->photoProfil,
                                    ] : null,
                                ];
                            })->values(),
                        ];
                    })
                    ->values();

                return [
                    'id'              => $el->id,
                    'nom'             => $el->nom,
                    'slug'            => $el->slug,
                    'documents_count' => $el->documents_count,
                    'types'           => $types,
                ];
            });

        return response()->json(['elements' => $elements]);
    }

    // ── GET /api/drive/documents — recherche globale ──────────────────────────
    public function documents(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = Document::whereNotNull('element_module_id')
            ->with([
                'elementModule.module.filiere',
                'elementModule.module.anneeModel',
                'uploader:id,nom,prenom,photoProfil',
            ]);

        if ($request->filled('filiere_id')) {
            $query->whereHas('elementModule.module', fn($q) =>
                $q->where('filiere_id', $request->filiere_id)
            );
        }

        if ($request->filled('annee')) {
            $query->whereHas('elementModule.module.anneeModel', fn($q) =>
                $q->where('label', $request->annee)
            );
        }

        if ($request->filled('type')) {
            $query->where('typeDocument', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }

        $docs = $query->latest()->paginate(20);

        $docs->getCollection()->transform(function ($d) use ($userId) {
            $d->is_saved = $d->isSavedBy($userId);
            return $d;
        });

        return response()->json($docs);
    }

    // ── GET /api/drive/documents/{document}/download ──────────────────────────
    public function download(Request $request, Document $document): JsonResponse
    {
        if (!$this->filter->canAccessDocument($request->user(), $document)) {
            return response()->json(['error' => 'Accès refusé à ce document.'], 403);
        }

        $document->increment('downloads_count');

        $url = $this->buildAzureUrl($document);

        return response()->json([
            'url'      => $url,
            'filename' => $document->nom ?? $document->titre,
            'mime'     => $document->mime_type,
        ]);
    }

    // ── GET /api/drive/documents/{document}/view ──────────────────────────────
    public function view(Request $request, Document $document): JsonResponse
    {
        if (!$this->filter->canAccessDocument($request->user(), $document)) {
            return response()->json(['error' => 'Accès refusé à ce document.'], 403);
        }

        $document->increment('views_count');

        $ext      = strtolower($document->extension ?? '');
        $fileUrl  = $this->buildAzureUrl($document);

        $viewMode = match(true) {
            in_array($ext, ['pdf'])                              => 'pdf',
            in_array($ext, ['png','jpg','jpeg','gif','webp'])    => 'image',
            in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx']) => 'office',
            default                                              => 'download',
        };

        return response()->json([
            'id'         => $document->id,
            'titre'      => $document->titre,
            'extension'  => $ext,
            'mime_type'  => $document->mime_type,
            'file_url'   => $fileUrl,
            'view_mode'  => $viewMode,
            'viewer_url' => $viewMode === 'office'
                ? 'https://docs.google.com/viewer?url=' . urlencode($fileUrl) . '&embedded=true'
                : null,
        ]);
    }

    // ── Shared helper — send new-document notifications to students ──────────
    private function notifyNewDocument(Document $document, Module $module, Filiere $filiere, ?string $anneeId): void
    {
        if (!$anneeId) {
            return;
        }

        $recipientIds = \App\Models\User::where('filiere', $filiere->nom)
            ->orWhere('filiere', $filiere->slug)
            ->get(['id', 'annee'])
            ->filter(fn($u) => !empty($u->annee))
            ->pluck('id')
            ->toArray();

        if (empty($recipientIds)) {
            return;
        }

        $notifService = app(NotificationService::class);
        $notifService->notifyNewDocument(
            $document->titre,
            $module->nom,
            $recipientIds,
        );
    }

    // ── Shared helper — build SAS-signed Azure URL (1h) ──────────────────────
    private function buildAzureUrl(Document $document): string    {
        $path      = $document->azure_path ?? '';
        $account   = config('filesystems.disks.azure.account', '');
        $key       = config('filesystems.disks.azure.key', '');
        $container = config('filesystems.disks.azure.container', 'ensias-hub-files');

        if (!$path || !$account || !$key) {
            return $document->azure_url ?? '';
        }

        try {
            $helper   = new \MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper($account, $key);
            $expiry   = new \DateTime('+1 hour');
            $sasToken = $helper->generateBlobServiceSharedAccessSignatureToken(
                'b',                          // RESOURCE_TYPE_BLOB
                $container . '/' . $path,     // resourceName (raw, unencoded)
                'r',                          // read permission
                $expiry
            );

            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

            return 'https://' . $account . '.blob.core.windows.net/'
                 . $container . '/' . $encodedPath . '?' . $sasToken;

        } catch (\Exception $e) {
            // Fallback: encoded direct URL (may fail if container truly private)
            $encodedPath = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));
            return rtrim(config('filesystems.disks.azure.url', ''), '/')
                 . '/' . $container . '/' . $encodedPath;
        }
    }

    // ── POST /api/drive/documents ─────────────────────────────────────────────
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file'              => 'required|file|max:102400',
            'titre'             => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'element_module_id' => 'required|exists:element_modules,id',
            'type'              => 'required|in:Cours,TD/TP,Anciens examens,Résumé,Projet,Autre',
        ]);

        try {
            $file    = $request->file('file');
            $element = ElementModule::with('module.filiere', 'module.anneeModel')
                                    ->findOrFail($request->element_module_id);

            $module  = $element->module;
            $filiere = $module->filiere;
            $annee   = $module->anneeModel;

            // Build Azure path: FILIERE/ANNEE/module-slug/element-slug/Type/uuid.ext
            $ext       = strtolower($file->getClientOriginalExtension());
            $azurePath = implode('/', [
                strtoupper($filiere->slug ?? $filiere->nom),
                $annee?->label ?? 'N/A',
                $module->slug  ?? Str::slug($module->nom),
                $element->slug ?? Str::slug($element->nom),
                $request->type,
                Str::uuid() . '.' . $ext,
            ]);

            // Upload vers Azure
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('azure')->put($azurePath, $stream, 'public');
            if (is_resource($stream)) {
                fclose($stream);
            }

            $azureUrl = rtrim(config('filesystems.disks.azure.url', ''), '/')
                      . '/' . config('filesystems.disks.azure.container', 'ensias-hub-files')
                      . '/' . $azurePath;

            $document = Document::create([
                'element_module_id' => $element->id,
                'uploader_id'       => $request->user()->id,
                'titre'             => $request->titre,
                'nom'               => $request->titre,
                'typeDocument'      => $request->type,
                'azure_path'        => $azurePath,
                'azure_url'         => $azureUrl,
                'urlStockage'       => $azurePath,
                'download_url'      => $azureUrl,
                'nom_original'      => $file->getClientOriginalName(),
                'extension'         => $ext,
                'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
                'taille'            => $file->getSize(),
                'statutValidation'  => 'Valide',
            ]);

            $document->load('uploader:id,nom,prenom,photoProfil');

            // Notify students of the same filiere + annee
            $this->notifyNewDocument($document, $module, $filiere, $annee?->id);

            return response()->json(['document' => $document], 201);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['error' => 'Erreur lors de l\'upload : ' . $e->getMessage()], 500);
        }
    }

    // ── DELETE /api/drive/documents/{document} ────────────────────────────────
    public function deleteDocument(Request $request, Document $document): JsonResponse
    {
        $user = $request->user();

        if ($document->uploader_id !== $user->id && ! $user->hasRole('superAdmin') && ! $user->hasRole('admin')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $document->forceDelete();

        return response()->json(['success' => true]);
    }

    // ── GET /api/drive/upload/form-data — metadata for upload modal ───────────
    public function uploadFormData(): JsonResponse
    {
        $filieres = Filiere::where('is_active', true)
            ->orderByRaw('is_tronc_commun ASC')
            ->orderBy('nom')
            ->get(['id', 'nom', 'slug', 'badge', 'is_tronc_commun']);

        $annees = Annee::orderBy('niveau')->get(['id', 'label', 'niveau']);

        return response()->json([
            'filieres' => $filieres,
            'annees'   => $annees,
            'types'    => ['Cours', 'TD/TP', 'Anciens examens', 'Résumé', 'Projet', 'Autre'],
        ]);
    }

    // ── GET /api/drive/upload/modules — modules filter for upload ────────────
    public function uploadModules(Request $request): JsonResponse
    {
        $query = Module::where('is_active', true)
            ->with([
                'filiere:id,nom,slug,badge',
                'elementModules:id,module_id,nom,slug',
            ])
            ->withCount(['driveDocuments as documents_count']);

        if ($request->filled('filiere_id')) {
            $query->where('filiere_id', $request->filiere_id);
        }

        if ($request->filled('annee_id')) {
            $query->where('annee_id', $request->annee_id);
        }

        if ($request->filled('semestre')) {
            $query->where('semestre', $request->semestre);
        }

        $modules = $query->orderBy('semestre')->orderBy('nom')->get();

        return response()->json(['modules' => $modules]);
    }
}

