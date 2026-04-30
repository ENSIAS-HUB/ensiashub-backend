<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderLine extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'quantite',
        'prixUnitaire',
        'totalLigne',
        'order_id',
        'menu_item_id',
    ];

    protected function casts(): array
    {
        return [
            'quantite' => 'integer',
            'prixUnitaire' => 'float',
            'totalLigne' => 'float',
        ];
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function menuItem(){
        return $this->belongsTo(MenuItem::class);
    }
}

 
