<?php

namespace App\Models;

use App\Traits\HasSocialFeatures;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasUuids, HasSocialFeatures, SoftDeletes;

    protected $fillable = [
        // Legacy fields (Google Drive sync)
        'titre',
        'nom',
        'format',
        'urlStockage',
        'typeDocument',
        'statutValidation',
        'uploader_id',
        'module_pedagogique_id',
        'publishedAt',
        'downloads_count',
        'gdrive_file_id',
        'mime_type',
        'preview_url',
        'download_url',
        'taille',
        // Azure Drive fields
        'azure_path',
        'azure_url',
        'extension',
        'filiere_id',
        'views_count',
        'semester',
        'year',
        // New hierarchy
        'element_module_id',
    ];

    protected $appends = ['size_formatted'];

    protected $casts = [
        'statutValidation' => 'string',
        'typeDocument'     => 'string',
        'publishedAt'      => 'datetime',
        'downloads_count'  => 'integer',
        'views_count'      => 'integer',
        'taille'           => 'integer',
        'year'             => 'integer',
    ];

    protected $attributes = [
        'downloads_count' => 0,
        'views_count'     => 0,
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    /**
     * Relation avec l'uploader (alias 'user' conservé pour compat.)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Alias 'uploader' pour les nouvelles routes Drive
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Relation avec le module pédagogique (legacy)
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_pedagogique_id');
    }

    /**
     * Relation avec l'élément de module (nouvelle hiérarchie Drive)
     */
    public function elementModule(): BelongsTo
    {
        return $this->belongsTo(ElementModule::class, 'element_module_id');
    }

    /**
     * Relation directe avec la filière (Drive)
     */
    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    /**
     * Relation avec la validation
     */
    public function validation(): HasOne
    {
        return $this->hasOne(DocumentReview::class, 'document_id');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getSizeFormattedAttribute(): string
    {
        $bytes = (int) ($this->taille ?? 0);
        if ($bytes < 1024)       return "{$bytes} B";
        if ($bytes < 1_048_576)  return round($bytes / 1024, 1).' KB';
        if ($bytes < 1_073_741_824) return round($bytes / 1_048_576, 1).' MB';
        return round($bytes / 1_073_741_824, 2).' GB';
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function estValide(): bool
    {
        return $this->statutValidation === 'Valide';
    }

    /**
     * URL de téléchargement — retourne l'URL Azure publique.
     * Si le conteneur est privé, décommentez la ligne temporaryUrl().
     */
    public function getDownloadUrl(int $minutesTtl = 30): string
    {
        if ($this->azure_url) {
            return $this->azure_url;
        }

        // Fallback sur le champ GDrive legacy
        return $this->download_url ?? $this->preview_url ?? $this->urlStockage ?? '';

        // Pour conteneur privé (SAS token) :
        // return Storage::disk('azure')->temporaryUrl($this->azure_path, now()->addMinutes($minutesTtl));
    }

    // ── Events ───────────────────────────────────────────────────────────────

    /**
     * Supprimer le blob Azure lors d'une suppression définitive.
     */
    protected static function booted(): void
    {
        // Suppression Azure gérée uniquement par les Jobs queue (DeleteBlobsFromAzure).
        // Ne pas supprimer ici → évite double suppression + requête bloquante.
    }

    // ── Static Helpers ───────────────────────────────────────────────────────

    /**
     * Clean a raw Google Drive file name into a readable title.
     */
    public static function cleanTitle(string $raw): string
    {
        $title = preg_replace('/\.[a-zA-Z]{2,5}$/', '', $raw);
        $title = preg_replace('/[\(\[]\d+[\)\]]/', '', $title ?? '');
        $title = str_replace('_', ' ', $title);
        $title = str_replace('/', ' ', $title);
        $title = preg_replace('/\s+-\s+/', ' ', $title);
        $title = preg_replace('/(?<=[a-zA-Z0-9])-(?=[a-zA-Z0-9])/', ' ', $title);
        $title = trim($title, " \t\n\r\0\x0B.,;:");
        $title = trim(preg_replace('/\s+/', ' ', $title));
        return ucwords(strtolower($title));
    }
}