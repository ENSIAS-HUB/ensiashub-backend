<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'numeroCommande',
        'statut',
        'tempsAttenteEstime',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'tempsAttenteEstime' => 'integer',
            'total'              => 'float',
        ];
    }

    /** Map DB statut to frontend status */
    public static function statutToStatus(string $statut): string
    {
        return match ($statut) {
            'EnAttente'     => 'en_attente',
            'EnPreparation' => 'en_preparation',
            'Prete'         => 'pret',
            'Recuperee'     => 'livre',
            'Annulee'       => 'annule',
            default         => 'en_attente',
        };
    }

    /** Map frontend status to DB statut */
    public static function statusToStatut(string $status): string
    {
        return match ($status) {
            'en_attente'     => 'EnAttente',
            'en_preparation' => 'EnPreparation',
            'pret'           => 'Prete',
            'livre'          => 'Recuperee',
            'annule'         => 'Annulee',
            default          => 'EnAttente',
        };
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function toApiArray(): array
    {
        return [
            'id'         => $this->id,
            'status'     => self::statutToStatus($this->statut),
            'total'      => (float) $this->total,
            'notes'      => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'items'      => $this->lines->map(fn ($l) => [
                'id'         => $l->id,
                'meal'       => $l->menuItem ? $l->menuItem->toApiArray() : null,
                'quantity'   => $l->quantite,
                'unit_price' => (float) $l->prixUnitaire,
            ])->values()->all(),
        ];
    }
}
