<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Publication extends Model
{
    use HasUuids;

    protected $table = 'publications';

    protected $fillable = [
        'contenu',
        'typeMedia',
        'statutValidation',
        'user_id',
        'groupe_id',
        'publishedAt',
    ];

    protected $casts = [
        'statutValidation' => 'string',
        'publishedAt' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec l'auteur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le groupe
     */
    public function groupe(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'groupe_id');
    }

    /**
     * Relation avec la validation
     */
    public function validation(): HasOne
    {
        return $this->hasOne(PublicationReview::class, 'publication_id');
    }

    /**
     * Relation avec les interactions
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class, 'publication_id');
    }

    /**
     * Relation avec les commentaires (via interactions)
     */
    public function commentaires(): HasMany
    {
        return $this->hasMany(Interaction::class, 'publication_id')->where('type', 'commentaire');
    }

    /**
     * Relation avec les réactions (via interactions)
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Interaction::class, 'publication_id')->where('type', 'reaction');
    }

    /**
     * Vérifier si la publication est validée
     */
    public function estValidee(): bool
    {
        return $this->statutValidation === 'Valide';
    }

    /**
     * Vérifier si la publication est en attente
     */
    public function estEnAttente(): bool
    {
        return $this->statutValidation === 'EnAttente';
    }
}