<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriveUploadService
{
    private const ALLOWED_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
        'ppt', 'pptx', 'txt', 'zip', 'rar',
        'png', 'jpg', 'jpeg', 'gif', 'mp4',
    ];

    /** 100 MB */
    private const MAX_SIZE_BYTES = 100 * 1024 * 1024;

    /**
     * Validate, upload to Azure Blob Storage, and persist metadata.
     */
    public function upload(
        UploadedFile $file,
        array        $metadata,
        string       $userId
    ): Document {
        $this->validate($file);

        $azurePath = $this->buildPath($file, $metadata);

        // Stream upload to Azure
        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk('azure')->put($azurePath, $stream, 'public');
        if (is_resource($stream)) {
            fclose($stream);
        }

        // Build public URL
        $azureUrl = rtrim(config('filesystems.disks.azure.url', ''), '/')
                  . '/'
                  . config('filesystems.disks.azure.container')
                  . '/'
                  . $azurePath;

        return Document::create([
            'uploader_id'          => $userId,
            'module_pedagogique_id' => $metadata['module_id'] ?? null,
            'filiere_id'           => $metadata['filiere_id'] ?? null,
            'titre'                => $metadata['title'],
            'nom'                  => $file->getClientOriginalName(),
            'description'          => $metadata['description'] ?? null,
            'azure_path'           => $azurePath,
            'azure_url'            => $azureUrl,
            'urlStockage'          => $azurePath,  // backward compat
            'download_url'         => $azureUrl,   // backward compat
            'mime_type'            => $file->getMimeType() ?? 'application/octet-stream',
            'taille'               => $file->getSize(),
            'format'               => strtolower($file->getClientOriginalExtension()),
            'extension'            => strtolower($file->getClientOriginalExtension()),
            'typeDocument'         => $this->mapDriveType($metadata['type'] ?? 'autre'),
            'semester'             => $metadata['semester'] ?? null,
            'year'                 => isset($metadata['year']) ? (int) $metadata['year'] : null,
            'statutValidation'     => 'Valide',  // Azure uploads skip review
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function validate(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new \InvalidArgumentException("Extension non autorisée : {$ext}");
        }

        if ($file->getSize() > self::MAX_SIZE_BYTES) {
            throw new \InvalidArgumentException('Fichier trop volumineux (max 100 MB)');
        }
    }

    private function buildPath(UploadedFile $file, array $metadata): string
    {
        // drive/{filiere}/{semester}/{module}/{year}/{uuid}.ext
        $parts = ['drive'];

        if (! empty($metadata['filiere_slug'])) {
            $parts[] = Str::slug($metadata['filiere_slug']);
        }
        if (! empty($metadata['semester'])) {
            $parts[] = strtoupper($metadata['semester']);
        }
        if (! empty($metadata['module_slug'])) {
            $parts[] = Str::slug($metadata['module_slug']);
        }

        $parts[] = date('Y');
        $parts[] = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return implode('/', $parts);
    }

    /**
     * Map new Drive types (lowercase) to legacy typeDocument enum values.
     */
    private function mapDriveType(string $type): string
    {
        return match (strtolower($type)) {
            'cours'         => 'Cours',
            'td', 'tp'      => 'TD',
            'examen', 'corrige' => 'Examen',
            default         => 'Autre',
        };
    }
}
