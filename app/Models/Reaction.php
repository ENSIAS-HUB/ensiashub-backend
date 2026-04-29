<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Interaction
{
    protected $table = 'reactions';

    protected $fillable = [
        'reaction',  // like, love, laugh, sad, angry
    ];

    /**
     * Boot method pour définir le type automatiquement
     */
    protected static function booted()
    {
        static::creating(function ($reaction) {
            $reaction->type = 'reaction';
        });
    }

    /**
     * Relation inverse vers l'interaction parente
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(Interaction::class, 'id');
    }

    /**
     * Types de réactions disponibles
     */
    public static function getTypesDisponibles(): array
    {
        return ['like', 'love', 'laugh', 'sad', 'angry'];
    }
}