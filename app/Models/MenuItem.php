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
        'description',
        'image_url',
        'categorie',
        'estDisponible',
        'prix',
    ];

    protected function casts(): array
    {
        return [
            'estDisponible' => 'boolean',
            'prix'          => 'float',
        ];
    }

    /** Normalized accessors for the frontend */
    public function toApiArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->nomPlat,
            'description' => $this->description,
            'price'       => (float) $this->prix,
            'category'    => $this->categorie,
            'available'   => (bool) $this->estDisponible,
            'image_url'   => $this->image_url,
        ];
    }

    public function orderLines()
    {
        return $this->hasMany(OrderLine::class);
    }
}
