<?php

namespace App\Jobs\Drive;

use App\Models\{Module, Document};
use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class RenameModuleInAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    /**
     * @param string $moduleId  UUID du module
     * @param string $oldSlug   Ancien slug (position [2] du chemin Azure)
     * @param string $newSlug   Nouveau slug
     */
    public function __construct(
        public readonly string $moduleId,
        public readonly string $oldSlug,
        public readonly string $newSlug,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        $module = Module::with('elementModules.documents')->findOrFail($this->moduleId);

        Log::info("[Drive] Renommage Azure module : {$this->oldSlug} → {$this->newSlug}");

        $count = 0;
        foreach ($module->elementModules as $element) {
            foreach ($element->documents as $doc) {
                if (!$doc->azure_path) continue;

                // Structure : FILIERE/ANNEE/module-slug/element-slug/type/fichier.ext
                $parts = explode('/', $doc->azure_path);
                if (!isset($parts[2]) || $parts[2] !== $this->oldSlug) continue;

                $parts[2] = $this->newSlug;
                $newPath  = implode('/', $parts);

                if ($azure->moveBlob($doc->azure_path, $newPath)) {
                    $doc->update([
                        'azure_path' => $newPath,
                        'azure_url'  => $azure->buildUrl($newPath),
                    ]);
                    $count++;
                }
            }
        }

        Log::info("[Drive] Renommage terminé : {$count} fichier(s) déplacé(s)");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] RenameModuleInAzure échoué (module={$this->moduleId}) : " . $e->getMessage());
    }
}
