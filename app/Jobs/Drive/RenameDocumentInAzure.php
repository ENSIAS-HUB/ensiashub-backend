<?php

namespace App\Jobs\Drive;

use App\Models\Document;
use App\Services\AzureSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;

class RenameDocumentInAzure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $documentId,
        public readonly string $newTitre,
    ) {}

    public function handle(AzureSyncService $azure): void
    {
        $doc = Document::findOrFail($this->documentId);

        if (!$doc->azure_path) {
            Log::warning("[Drive] RenameDocumentInAzure : pas de azure_path pour doc {$this->documentId}");
            return;
        }

        $dir      = dirname($doc->azure_path);
        $ext      = $doc->extension ?? pathinfo($doc->azure_path, PATHINFO_EXTENSION);
        $newPath  = $dir . '/' . $this->newTitre . ($ext ? '.' . $ext : '');

        if ($newPath === $doc->azure_path) return;

        if ($azure->moveBlob($doc->azure_path, $newPath)) {
            $doc->update([
                'azure_path' => $newPath,
                'azure_url'  => $azure->buildUrl($newPath),
            ]);
            Log::info("[Drive] Document renommé dans Azure : {$doc->azure_path} → {$newPath}");
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error("[Drive] RenameDocumentInAzure échoué (doc={$this->documentId}) : " . $e->getMessage());
    }
}
