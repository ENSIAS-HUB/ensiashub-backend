<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasUuids;

    protected $table = 'modules';

    protected $fillable = [
        'filiere_id',
        'nom',
        'semestre',
        'annee',
    ];

    protected $casts = [
        'annee' => 'integer',
        'semestre' => 'string',
    ];

    /**
     * Relation avec la filière
     */
    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    /**
     * Relation avec les documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'module_pedagogique_id');
    }

    /**
     * Obtenir les documents validés
     */
    public function getDocumentsValides()
    {
        return $this->documents()->where('statutValidation', 'Valide')->get();
    }
}