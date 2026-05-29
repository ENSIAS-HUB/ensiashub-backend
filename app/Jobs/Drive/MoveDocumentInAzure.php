<?php

namespace App\Jobs\Drive;

use App\Models\Document;
use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class MoveDocumentInAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    /**
     * @param string $documentId     UUID du document
     * @param string $newAzureFolder Nouveau préfixe de dossier (ex: "TC/1A/S1/Algo/cours")
     * @param string $newTypeDocument Nouveau typeDocument (cours, td, examen…)
     */
    public function __construct(
        public readonly string $documentId,
        public readonly string $newAzureFolder,
        public readonly string $newTypeDocument,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        $doc = Document::findOrFail($this->documentId);

        if (!$doc->azure_path) {
            Log::warning("[Drive] MoveDocumentInAzure : pas de azure_path pour doc {$this->documentId}");
            return;
        }

        $filename = basename($doc->azure_path);
        $newPath  = rtrim($this->newAzureFolder, '/') . '/' . $filename;

        if ($newPath === $doc->azure_path) {
            $doc->update(['typeDocument' => $this->newTypeDocument]);
            return;
        }

        if ($azure->moveBlob($doc->azure_path, $newPath)) {
            $doc->update([
                'azure_path'   => $newPath,
                'azure_url'    => $azure->buildUrl($newPath),
                'typeDocument' => $this->newTypeDocument,
            ]);
            Log::info("[Drive] Document déplacé : {$doc->azure_path} → {$newPath}");
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] MoveDocumentInAzure échoué (doc={$this->documentId}) : " . $e->getMessage());
    }
}
