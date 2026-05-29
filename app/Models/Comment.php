<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'commentable_type',
        'commentable_id',
        'parent_id',
        'content',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->with('user:id,nom,prenom,avatar_url,photoProfil')
                    ->latest();
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Masque le contenu pour les commentaires supprimés
     * mais garde le thread intact (soft delete).
     */
    public function getContentAttribute(string $value): string
    {
        if ($this->trashed()) {
            return '[Ce commentaire a été supprimé]';
        }
        return $value;
    }
}
