<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationReview extends Model
{
    use HasUuids;

    protected $table = 'publication_reviews';

    protected $fillable = [
        'publication_id',
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
     * Relation avec la publication
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    /**
     * Relation avec le modérateur
     */
    public function moderateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderateur_id');
    }
}