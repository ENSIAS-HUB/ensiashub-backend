<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commentaire extends Interaction
{
    protected $table = 'commentaires';

    protected $fillable = [
        'contenu',
    ];

    /**
     * Boot method pour définir le type automatiquement
     */
    protected static function booted()
    {
        static::creating(function ($commentaire) {
            $commentaire->type = 'commentaire';
        });
    }

    /**
     * Relation inverse vers l'interaction parente
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class, 'id');
    }
}