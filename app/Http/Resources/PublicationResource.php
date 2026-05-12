<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicationResource extends JsonResource
{
    private static array $statusMap = [
        'Valide'    => 'approved',
        'EnAttente' => 'pending',
        'Rejete'    => 'rejected',
    ];

    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'content'         => $this->contenu,
            'media_url'       => $this->typeMedia,
            'status'          => self::$statusMap[$this->statutValidation] ?? 'pending',
            'author'          => new UserResource($this->whenLoaded('user')),
            'group'           => $this->when(
                $this->relationLoaded('groupe') && $this->groupe,
                fn () => (new GroupResource($this->groupe))->toArray(request())
            ),
            'reactions_count' => (int) ($this->reactions_count ?? 0),
            'comments_count'  => (int) ($this->comments_count ?? 0),
            'user_reacted'    => (bool) ($this->user_reacted ?? false),
            'created_at'      => $this->created_at,
        ];
    }
}
