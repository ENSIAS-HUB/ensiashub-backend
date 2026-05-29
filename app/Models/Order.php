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
            'EnAttente'     => 'pending',
            'Confirme'      => 'confirmed',
            'EnPreparation' => 'preparing',
            'Prete'         => 'ready',
            'Recuperee'     => 'completed',
            'Annulee'       => 'cancelled',
            default         => 'pending',
        };
    }

    /** Map frontend status to DB statut */
    public static function statusToStatut(string $status): string
    {
        return match ($status) {
            'pending'    => 'EnAttente',
            'confirmed'  => 'Confirme',
            'preparing'  => 'EnPreparation',
            'ready'      => 'Prete',
            'completed'  => 'Recuperee',
            'cancelled'  => 'Annulee',
            // Legacy aliases (eats frontend compatibility)
            'en_attente'     => 'EnAttente',
            'en_preparation' => 'EnPreparation',
            'pret'           => 'Prete',
            'livre'          => 'Recuperee',
            'annule'         => 'Annulee',
            default          => 'EnAttente',
        };
    }

    /** Allowed status transitions */
    public static function allowedTransitions(): array
    {
        return [
            'pending'   => ['confirmed', 'cancelled'],
            'confirmed' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready'     => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $current = self::statutToStatus($this->statut);
        return in_array($newStatus, self::allowedTransitions()[$current] ?? [], true);
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

    public function toKitchenArray(): array
    {
        $user = $this->user;
        return [
            'id'          => $this->id,
            'status'      => self::statutToStatus($this->statut),
            'total_price' => (float) $this->total,
            'notes'       => $this->notes,
            'created_at'  => $this->created_at?->toISOString(),
            'customer'    => $user ? [
                'id'      => $user->id,
                'name'    => trim(($user->prenom ?? '') . ' ' . ($user->nom ?? '')),
                'filiere' => $user->filiere,
                'annee'   => $user->annee,
            ] : null,
            'items' => $this->lines->map(fn ($l) => [
                'id'                   => $l->id,
                'name'                 => $l->menuItem?->nomPlat ?? 'Plat supprimé',
                'quantity'             => $l->quantite,
                'unit_price'           => (float) $l->prixUnitaire,
                'special_instructions' => $l->special_instructions ?? null,
            ])->values()->all(),
        ];
    }
}

