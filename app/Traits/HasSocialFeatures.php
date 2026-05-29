<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\SavedItem;
use App\Models\Share;
use App\Models\Report;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait polymorphique réutilisable.
 * Appliquer sur : Publication, Document, Project, MenuItem.
 */
trait HasSocialFeatures
{
    // ── Commentaires ─────────────────────────────────────────────────────────

    /**
     * Commentaires racine (sans parent) avec leurs réponses imbriquées.
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')
                    ->whereNull('parent_id')
                    ->with([
                        'user:id,nom,prenom,avatar_url,photoProfil',
                        'replies.user:id,nom,prenom,avatar_url,photoProfil',
                    ])
                    ->withCount('replies')
                    ->latest();
    }

    /**
     * Tous les commentaires (racine + réponses).
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // ── Sauvegarde ───────────────────────────────────────────────────────────

    public function savedItems(): MorphMany
    {
        return $this->morphMany(SavedItem::class, 'saveable');
    }

    // ── Partages ─────────────────────────────────────────────────────────────

    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    // ── Signalements ─────────────────────────────────────────────────────────

    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * L'utilisateur a-t-il sauvegardé cet item ?
     */
    public function isSavedBy(string $userId): bool
    {
        return $this->savedItems()
                    ->where('user_id', $userId)
                    ->exists();
    }

    /**
     * Nombre total de commentaires (racine + réponses).
     */
    public function commentsCount(): int
    {
        return $this->allComments()->count();
    }
}
