<?php

namespace App\Jobs\Drive;

use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class DeleteBlobsFromAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    /**
     * @param string[] $azurePaths  Chemins à supprimer (collectés avant la suppression DB)
     * @param string   $label       Nom pour les logs
     */
    public function __construct(
        public readonly array  $azurePaths,
        public readonly string $label,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        Log::info("[Drive] Suppression Azure ({$this->label}) : " . count($this->azurePaths) . " fichier(s)");

        $deleted = 0;
        foreach ($this->azurePaths as $path) {
            if ($path && $azure->deleteBlob($path)) {
                $deleted++;
            }
        }

        Log::info("[Drive] Suppression terminée : {$deleted} blob(s) supprimé(s)");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] DeleteBlobsFromAzure échoué ({$this->label}) : " . $e->getMessage());
    }
}
