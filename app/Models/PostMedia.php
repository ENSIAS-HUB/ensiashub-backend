<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasUuids;

    protected $table = 'post_media';

    protected $fillable = [
        'publication_id',
        'url',
        'type',
        'thumbnail_url',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }
}
