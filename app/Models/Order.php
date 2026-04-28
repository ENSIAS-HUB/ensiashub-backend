<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Concerns\HasUuids;



class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'numeroCommande',
        'statut',
        'tempsAttenteEstime',
    ];

    protected function casts(): array
    {
        return [
            'tempsAttenteEstime' => 'integer',
        ];
    }



}
