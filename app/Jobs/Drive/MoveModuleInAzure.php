<?php

namespace App\Jobs\Drive;

use App\Models\Module;
use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class MoveModuleInAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 300;

    /**
     * @param string $moduleId       UUID du module
     * @param string $oldAnneeLabel  Ancien label d'année (position [1] du chemin Azure, ex: "1A")
     * @param string $newAnneeLabel  Nouveau label
     */
    public function __construct(
        public readonly string $moduleId,
        public readonly string $oldAnneeLabel,
        public readonly string $newAnneeLabel,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        $module = Module::with('elementModules.documents')->findOrFail($this->moduleId);

        Log::info("[Drive] Déplacement Azure module {$module->nom} : {$this->oldAnneeLabel} → {$this->newAnneeLabel}");

        $count = 0;
        foreach ($module->elementModules as $element) {
            foreach ($element->documents as $doc) {
                if (!$doc->azure_path) continue;

                // Structure : FILIERE/ANNEE/module-slug/element-slug/type/fichier.ext
                $parts = explode('/', $doc->azure_path);
                if (!isset($parts[1]) || $parts[1] !== $this->oldAnneeLabel) continue;

                $parts[1] = $this->newAnneeLabel;
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

        Log::info("[Drive] Déplacement terminé : {$count} fichier(s) mis à jour");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] MoveModuleInAzure échoué (module={$this->moduleId}) : " . $e->getMessage());
    }
}
