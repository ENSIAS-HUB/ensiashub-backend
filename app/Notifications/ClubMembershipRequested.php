<?php

namespace App\Notifications;

use App\Models\Group;
use App\Models\User;
use Illuminate\Notifications\Notification;

class ClubMembershipRequested extends Notification
{
    public function __construct(
        public readonly User  $requester,
        public readonly Group $group,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'club_membership_requested',
            'requester_id'   => $this->requester->id,
            'requester_name' => trim(($this->requester->prenom ?? '') . ' ' . ($this->requester->nom ?? '')),
            'requester_avatar' => $this->requester->photoProfil,
            'group_id'       => $this->group->id,
            'group_name'     => $this->group->nom,
            'group_slug'     => $this->group->slug,
            'message'        => trim(($this->requester->prenom ?? '') . ' ' . ($this->requester->nom ?? ''))
                                . ' a demandé à rejoindre ' . $this->group->nom,
        ];
    }
}
