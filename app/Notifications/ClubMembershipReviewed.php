<?php

namespace App\Notifications;

use App\Models\Group;
use Illuminate\Notifications\Notification;

class ClubMembershipReviewed extends Notification
{
    public function __construct(
        public readonly Group  $group,
        public readonly string $decision,   // 'approved' | 'rejected'
        public readonly ?string $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $message = $this->decision === 'approved'
            ? 'Ta demande pour ' . $this->group->nom . ' a été acceptée 🎉'
            : 'Ta demande pour ' . $this->group->nom . ' a été refusée'
              . ($this->reason ? ' : ' . $this->reason : '');

        return [
            'type'       => 'club_membership_reviewed',
            'group_id'   => $this->group->id,
            'group_name' => $this->group->nom,
            'group_slug' => $this->group->slug,
            'decision'   => $this->decision,
            'reason'     => $this->reason,
            'message'    => $message,
        ];
    }
}
