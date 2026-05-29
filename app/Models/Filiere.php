<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    use HasUuids;

    protected $fillable = ['nom', 'code', 'slug', 'badge', 'description', 'is_active', 'is_tronc_commun'];

    protected $casts = [
        'is_active'       => 'boolean',
        'is_tronc_commun' => 'boolean',
    ];

    /**
     * Relation avec les modules
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Documents rattachés directement à la filière
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'filiere_id');
    }
}