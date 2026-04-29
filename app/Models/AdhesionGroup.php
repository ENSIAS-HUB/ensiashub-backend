<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdhesionGroup extends Model
{
    use HasUuids;

    protected $table = 'adhesion_groups';

    protected $fillable = [
        'user_id',
        'group_id',
        'statut',
        'role',
        'joinedAt',
        'reviewedAt',
        'motifDecision',
    ];

    protected $casts = [
        'joinedAt' => 'datetime',
        'reviewedAt' => 'datetime',
        'statut' => 'string',
        'role' => 'string',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le groupe
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Vérifier si l'adhésion est approuvée
     */
    public function estApprouvee(): bool
    {
        return $this->statut === 'Approuve';
    }

    /**
     * Vérifier si l'adhésion est en attente
     */
    public function estEnAttente(): bool
    {
        return $this->statut === 'EnAttente';
    }

    /**
     * Vérifier si l'utilisateur est banni
     */
    public function estBanni(): bool
    {
        return $this->statut === 'Banni';
    }

    /**
     * Vérifier si l'utilisateur est modérateur
     */
    public function estModerateur(): bool
    {
        return $this->role === 'Moderateur' && $this->estApprouvee();
    }
}