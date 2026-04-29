<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filiere extends Model
{
    use HasUuids;

    protected $fillable = ['nom'];

    /**
     * Relation avec les modules
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
}