<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentReview extends Model
{
    use HasUuids;

    protected $table = 'document_reviews';

    protected $fillable = [
        'document_id',
        'moderateur_id',
        'statut',
        'reviewedAt',
        'motif',
    ];

    protected $casts = [
        'reviewedAt' => 'datetime',
        'statut' => 'string',
    ];

    /**
     * Relation avec le document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Relation avec le modérateur
     */
    public function moderateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderateur_id');
    }
}