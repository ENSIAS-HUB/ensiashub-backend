<?php

namespace App\Jobs\Drive;

use App\Models\ElementModule;
use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class RenameElementInAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    /**
     * @param string $elementId  UUID de l'élément
     * @param string $oldSlug    Ancien slug (position [3] du chemin Azure)
     * @param string $newSlug    Nouveau slug
     */
    public function __construct(
        public readonly string $elementId,
        public readonly string $oldSlug,
        public readonly string $newSlug,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        $element = ElementModule::with('documents')->findOrFail($this->elementId);

        Log::info("[Drive] Renommage Azure élément : {$this->oldSlug} → {$this->newSlug}");

        $count = 0;
        foreach ($element->documents as $doc) {
            if (!$doc->azure_path) continue;

            // Structure : FILIERE/ANNEE/module-slug/element-slug/type/fichier.ext
            $parts = explode('/', $doc->azure_path);
            if (!isset($parts[3]) || $parts[3] !== $this->oldSlug) continue;

            $parts[3] = $this->newSlug;
            $newPath  = implode('/', $parts);

            if ($azure->moveBlob($doc->azure_path, $newPath)) {
                $doc->update([
                    'azure_path' => $newPath,
                    'azure_url'  => $azure->buildUrl($newPath),
                ]);
                $count++;
            }
        }

        Log::info("[Drive] Renommage élément terminé : {$count} fichier(s) déplacé(s)");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] RenameElementInAzure échoué (element={$this->elementId}) : " . $e->getMessage());
    }
}
