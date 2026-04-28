<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class IotDevice extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'idMateriel',
        'typeCapteur',
        'emplacement',
        'statutActuel',
        'laundry_machine_id'
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

    public function laundryMachine(): BelongsTo
    {
        return $this->belongsTo(LaundryMachine::class);
    }
}

