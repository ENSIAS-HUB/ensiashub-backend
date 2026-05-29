<?php

namespace App\Services;

use Illuminate\Support\Facades\{Storage, Log};

class AzureSyncService
{
    private string $disk = 'azure';

    /**
     * Déplacer (renommer) un seul blob Azure.
     * Copie → supprime l'original.
     */
    public function moveBlob(string $oldPath, string $newPath): bool
    {
        try {
            if (!Storage::disk($this->disk)->exists($oldPath)) {
                Log::warning("[Azure] Blob introuvable : {$oldPath}");
                return false;
            }

            $stream = Storage::disk($this->disk)->readStream($oldPath);
            Storage::disk($this->disk)->put($newPath, $stream);
            Storage::disk($this->disk)->delete($oldPath);

            Log::info("[Azure] Blob déplacé : {$oldPath} → {$newPath}");
            return true;

        } catch (\Exception $e) {
            Log::error("[Azure] moveBlob : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un seul blob Azure.
     */
    public function deleteBlob(string $path): bool
    {
        try {
            if (Storage::disk($this->disk)->exists($path)) {
                Storage::disk($this->disk)->delete($path);
                Log::info("[Azure] Blob supprimé : {$path}");
            }
            return true;
        } catch (\Exception $e) {
            Log::error("[Azure] deleteBlob : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Déplacer tous les blobs d'un préfixe vers un nouveau préfixe.
     * Retourne les stats : moved, failed, paths mapping.
     */
    public function moveFolder(string $oldPrefix, string $newPrefix): array
    {
        $results = ['moved' => 0, 'failed' => 0, 'paths' => []];

        try {
            $files = Storage::disk($this->disk)->allFiles($oldPrefix);

            foreach ($files as $oldPath) {
                $relativePath = substr($oldPath, strlen($oldPrefix));
                $newPath      = $newPrefix . $relativePath;

                if ($this->moveBlob($oldPath, $newPath)) {
                    $results['moved']++;
                    $results['paths'][$oldPath] = $newPath;
                } else {
                    $results['failed']++;
                }
            }

        } catch (\Exception $e) {
            Log::error("[Azure] moveFolder : " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Supprimer tous les blobs d'un préfixe (dossier entier).
     * Retourne le nombre de blobs supprimés.
     */
    public function deleteFolder(string $prefix): int
    {
        $deleted = 0;

        try {
            $files = Storage::disk($this->disk)->allFiles($prefix);
            foreach ($files as $file) {
                if ($this->deleteBlob($file)) {
                    $deleted++;
                }
            }
        } catch (\Exception $e) {
            Log::error("[Azure] deleteFolder : " . $e->getMessage());
        }

        return $deleted;
    }

    /**
     * Construire l'URL encodée depuis un chemin Azure.
     * Chaque segment est rawurlencode sauf les slashes.
     */
    public function buildUrl(string $azurePath): string
    {
        $encoded = implode('/', array_map(
            'rawurlencode',
            explode('/', ltrim($azurePath, '/'))
        ));

        $base      = rtrim(
            config('filesystems.disks.azure.url')
            ?? ('https://' . config('filesystems.disks.azure.name') . '.blob.core.windows.net'),
            '/'
        );
        $container = config('filesystems.disks.azure.container', env('AZURE_STORAGE_CONTAINER'));

        return "{$base}/{$container}/{$encoded}";
    }
}
