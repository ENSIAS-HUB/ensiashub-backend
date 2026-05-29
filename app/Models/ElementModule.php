<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElementModule extends Model
{
    use HasUuids;

    protected $table = 'element_modules';

    protected $fillable = ['module_id', 'nom', 'slug'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'element_module_id');
    }
}
