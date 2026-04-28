<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LaundryMachine extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'numeroMachine',
        'type',
        'status'
    ];

    protected function casts(): array
    {
        return [
        ];
    }

    public function iotDevice(): HasOne{
        return $this->hasOne(IotDevice::class);
    }
}
