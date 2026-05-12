<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
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
    ];

    protected $casts = [
        'statutValidation' => 'string',
        'typeDocument' => 'string',
        'publishedAt' => 'datetime',
        'downloads_count' => 'integer',
    ];

    protected $attributes = [
        'downloads_count' => 0,
    ];

    /**
     * Relation avec l'uploader
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Relation avec le module
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_pedagogique_id');
    }

    /**
     * Relation avec la validation
     */
    public function validation(): HasOne
    {
        return $this->hasOne(DocumentReview::class, 'document_id');
    }

    /**
     * Vérifier si le document est validé
     */
    public function estValide(): bool
    {
        return $this->statutValidation === 'Valide';
    }

    /**
     * Clean a raw Google Drive file name into a readable title.
     *
     * Steps:
     *  1. Remove common file extensions (.pdf, .docx, …)
     *  2. Remove GDrive copy suffixes: (1) (2) [1] [2] …
     *  3. Replace underscores with spaces
     *  4. Replace hyphens surrounded by alphanumerics with spaces
     *  5. Collapse multiple spaces and trim
     *  6. Capitalise first letter of each word
     */
    public static function cleanTitle(string $raw): string
    {
        // 1. Remove file extension
        $title = preg_replace('/\.[a-zA-Z]{2,5}$/', '', $raw);

        // 2. Remove Google Drive copy suffixes like (1), [1], (2), [2], …
        $title = preg_replace('/[\(\[]\d+[\)\]]/', '', $title ?? '');

        // 3. Replace underscores with spaces
        $title = str_replace('_', ' ', $title);

        // 4. Replace forward slashes with spaces
        $title = str_replace('/', ' ', $title);

        // 5. Replace " - " (spaced hyphen) with a single space
        $title = preg_replace('/\s+-\s+/', ' ', $title);

        // 6. Replace hyphens that sit between alphanumeric chars with spaces
        //    (e.g. "POO-25" → "POO 25")
        $title = preg_replace('/(?<=[a-zA-Z0-9])-(?=[a-zA-Z0-9])/', ' ', $title);

        // 7. Remove any remaining trailing/leading punctuation (dots, commas…)
        $title = trim($title, " \t\n\r\0\x0B.,;:");

        // 8. Collapse multiple whitespace characters and trim
        $title = trim(preg_replace('/\s+/', ' ', $title));

        // 9. Capitalise first letter of each word (after lowercasing everything)
        return ucwords(strtolower($title));
    }
}