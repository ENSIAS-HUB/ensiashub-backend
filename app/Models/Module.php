<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Module extends Model
{
    use HasUuids;

    protected $table = 'modules';

    protected $fillable = [
        'filiere_id',
        'annee_id',
        'nom',
        'slug',
        'semestre',
        'annee',
        'filiere_specifique',
        'description',
        'is_active',
    ];

    protected $casts = [
        'annee'     => 'integer',
        'semestre'  => 'string',
        'is_active' => 'boolean',
    ];

    public function filiere(): BelongsTo
    {
        return $this->belongsTo(Filiere::class, 'filiere_id');
    }

    public function anneeModel(): BelongsTo
    {
        return $this->belongsTo(Annee::class, 'annee_id');
    }

    /** Elements du module (nouvelle hiérarchie) */
    public function elementModules(): HasMany
    {
        return $this->hasMany(ElementModule::class);
    }

    /** Documents via element_modules (nouvelle hiérarchie Azure Drive) */
    public function driveDocuments(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, ElementModule::class, 'module_id', 'element_module_id');
    }

    /** Documents legacy (via module_pedagogique_id) */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'module_pedagogique_id');
    }

    public function getDocumentsValides()
    {
        return $this->documents()->where('statutValidation', 'Valide')->get();
    }
}