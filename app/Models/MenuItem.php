<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class MenuItem extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'nomPlat',
        'categorie',
        'estDisponible',
        'prix',
    ];

    protected function casts(): array
    {
        return [
            'estDisponible' => 'boolean',
        ];
    }
}
