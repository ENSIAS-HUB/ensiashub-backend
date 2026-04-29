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
}