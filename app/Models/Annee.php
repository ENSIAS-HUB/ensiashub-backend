<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annee extends Model
{
    use HasUuids;

    protected $fillable = ['label', 'niveau'];

    protected $casts = [
        'niveau' => 'integer',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
}
