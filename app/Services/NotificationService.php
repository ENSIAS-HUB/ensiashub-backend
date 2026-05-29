<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Simple notification service that writes directly to the `notifications` table
 * (Laravel's standard DatabaseNotification format).
 *
 * Schema: id (uuid), type (string), notifiable_type, notifiable_id,
 *         data (json), read_at, created_at, updated_at
 */
class NotificationService
{
    // ── Low-level sender ──────────────────────────────────────────────────────

    /**
     * Persist a notification for a given user.
     * Skips self-notifications (when the authenticated actor is the recipient).
     */
    public function send(
        User   $recipient,
        string $type,
        string $title,
        string $body,
        array  $data       = [],
        string $actionUrl  = '',
    ): void {
        // Never notify yourself
        $actorId = auth()->id();
        if ($actorId && $actorId === $recipient->id) {
            return;
        }

        DB::table('notifications')->insert([
            'id'              => (string) Str::uuid(),
            'type'            => $type,
            'notifiable_type' => User::class,
            'notifiable_id'   => $recipient->id,
            'data'            => json_encode([
                'title'      => $title,
                'body'       => $body,
                'action_url' => $actionUrl,
                ...$data,
            ]),
            'read_at'         => null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ── Semantic helpers ──────────────────────────────────────────────────────

    public function notifyReaction(
        User   $author,
        string $emoji,
        string $contentTitle,
        string $actionUrl = '',
    ): void {
        $reactor = auth()->user();
        if (!$reactor) {
            return;
        }

        $reactorName = trim(($reactor->prenom ?? '') . ' ' . ($reactor->nom ?? ''));

        $this->send(
            $author,
            'reaction',
            "{$reactorName} a réagi à votre publication",
            "{$emoji} à « {$contentTitle} »",
            ['emoji' => $emoji, 'reactor_id' => $reactor->id],
            $actionUrl,
        );
    }

    public function notifyComment(
        User   $author,
        string $contentTitle,
        string $actionUrl = '',
    ): void {
        $commenter = auth()->user();
        if (!$commenter) {
            return;
        }

        $commenterName = trim(($commenter->prenom ?? '') . ' ' . ($commenter->nom ?? ''));

        $this->send(
            $author,
            'comment',
            "{$commenterName} a commenté votre publication",
            "Commentaire sur « {$contentTitle} »",
            ['commenter_id' => $commenter->id],
            $actionUrl,
        );
    }

    /**
     * Notify a list of students that a new document is available.
     */
    public function notifyNewDocument(
        string $documentTitle,
        string $moduleName,
        array  $recipientIds,
        string $actionUrl = '',
    ): void {
        if (empty($recipientIds)) {
            return;
        }

        $now  = now();
        $rows = [];

        foreach ($recipientIds as $userId) {
            // No self-notification
            if (auth()->id() && $userId === auth()->id()) {
                continue;
            }

            $rows[] = [
                'id'              => (string) Str::uuid(),
                'type'            => 'new_document',
                'notifiable_type' => User::class,
                'notifiable_id'   => $userId,
                'data'            => json_encode([
                    'title'      => "Nouveau document : {$documentTitle}",
                    'body'       => "Dans le module {$moduleName}",
                    'action_url' => $actionUrl,
                ]),
                'read_at'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('notifications')->insert($rows);
        }
    }
}
