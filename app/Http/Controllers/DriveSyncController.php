<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriveSyncController extends Controller
{
    /**
     * Upsert a filière (semester folder: S1, S2, …).
     */
    public function syncFiliere(Request $request): JsonResponse
    {
        $filiere = Filiere::firstOrCreate(
            ['nom' => $request->string('nom')->toString()],
            ['code' => $request->input('code')]
        );

        return response()->json(['data' => $filiere]);
    }

    /**
     * Upsert a module (subject folder inside a semester).
     */
    public function syncModule(Request $request): JsonResponse
    {
        $module = Module::firstOrCreate(
            [
                'nom'        => $request->string('nom')->toString(),
                'filiere_id' => $request->input('filiere_id'),
                'semestre'   => $request->input('semestre'),
            ],
            [
                'filiere_specifique' => $request->input('filiere_specifique'),
            ]
        );

        return response()->json(['data' => $module]);
    }

    /**
     * Upsert a document keyed by its Google Drive file ID.
     * Skips creation if the gdrive_file_id already exists (idempotent re-runs).
     */
    public function syncDocument(Request $request): JsonResponse
    {
        // Skip Google Docs native formats — they have no downloadable binary
        $skipMimes = [
            'application/vnd.google-apps.document',
            'application/vnd.google-apps.spreadsheet',
            'application/vnd.google-apps.presentation',
            'application/vnd.google-apps.form',
        ];
        if (in_array($request->input('mime_type'), $skipMimes)) {
            return response()->json(['data' => null, 'skipped' => true]);
        }

        // Map Python script's lowercase type → DB enum values
        $typeMap = [
            'cours'   => 'Cours',
            'td'      => 'TD',
            'examen'  => 'Examen',
            'resume'  => 'Autre',
        ];
        $typeDocument = $typeMap[$request->input('typeDocument')] ?? 'Autre';

        $statut = match ($request->input('statutValidation')) {
            'Approuvé', 'Valide' => 'Valide',
            'Rejete'             => 'Rejete',
            default              => 'EnAttente',
        };

        $doc = Document::updateOrCreate(
            ['gdrive_file_id' => $request->input('gdrive_file_id')],
            [
                'titre'                 => Document::cleanTitle($request->input('titre', '')),
                'module_pedagogique_id' => $request->input('module_pedagogique_id'),
                'typeDocument'          => $typeDocument,
                'taille'                => $request->input('taille'),
                'mime_type'             => $request->input('mime_type'),
                'preview_url'           => $request->input('preview_url'),
                'urlStockage'           => $request->input('preview_url'),
                'download_url'          => $request->input('download_url'),
                'statutValidation'      => $statut,
            ]
        );

        return response()->json(['data' => $doc]);
    }
}
