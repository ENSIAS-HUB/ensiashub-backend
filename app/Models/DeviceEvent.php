<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class DeviceEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'iot_device_id',
        'timestamp',
        'valeurBrute',
        'statutDerive',
    ];

    protected function casts():array
    {
        return [
            'timestamp' => 'datetime',
            'valeurBrute' => 'string',
            'statutDerive' => 'string',
        ];
    }

    public function iotDevice():BelongsTo
    {
        return $this->belongsTo(IotDevice::class);
    }


}
