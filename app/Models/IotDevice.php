<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IotDevice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'idMateriel',
        'typeCapteur',
        'emplacement',
        'statutActuel',
    ];
    
    protected function casts(): array
    {
        return [
            'statutActuel' => 'boolean',
        ];
    }

    public function envoyerStatut(): bool
    {
        return true;
    }
}

