<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Share extends Model
{
    protected $fillable = [
        'user_id',
        'shareable_type',
        'shareable_id',
        'channel',
        'target_group_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'target_group_id');
    }
}
