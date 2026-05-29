<?php

namespace App\Models;

use App\Traits\HasSocialFeatures;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasSocialFeatures;
    protected $fillable = [
        'user_id', 'titre', 'description', 'tech_stack',
        'github_url', 'live_url', 'image_url',
        'is_featured', 'status', 'date_debut', 'date_fin', 'ordre',
    ];

    protected $casts = [
        'tech_stack'  => 'array',
        'is_featured' => 'boolean',
        'date_debut'  => 'date',
        'date_fin'    => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
