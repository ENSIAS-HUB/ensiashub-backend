<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    // ── GET /api/notifications ────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $notifs = DatabaseNotification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        $notifs->getCollection()->transform(function ($n) {
            $data = is_array($n->data) ? $n->data : json_decode($n->data, true);
            return [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $data['title']      ?? '',
                'body'       => $data['body']        ?? '',
                'action_url' => $data['action_url']  ?? null,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at,
            ];
        });

        return response()->json($notifs);
    }

    // ── GET /api/notifications/unread-count ───────────────────────────────────
    public function unreadCount(Request $request): JsonResponse
    {
        $count = DatabaseNotification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    // ── POST /api/notifications/{id}/read ─────────────────────────────────────
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notif = DatabaseNotification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $request->user()->id)
            ->findOrFail($id);

        $notif->markAsRead();

        return response()->json(['success' => true]);
    }

    // ── POST /api/notifications/read-all ─────────────────────────────────────
    public function markAllRead(Request $request): JsonResponse
    {
        DB::table('notifications')
            ->where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ── GET /api/notifications/stream — Server-Sent Events ───────────────────
    public function stream(Request $request): Response
    {
        $userId    = $request->user()->id;
        $startTime = time();
        $maxTime   = 5 * 60; // 5 minutes max

        return response()->stream(function () use ($userId, $startTime, $maxTime) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            $lastCount = -1;

            while (true) {
                if (time() - $startTime >= $maxTime) {
                    echo "event: close\ndata: {}\n\n";
                    flush();
                    break;
                }

                // Heartbeat every 30s
                $elapsed = time() - $startTime;
                if ($elapsed > 0 && $elapsed % 30 === 0) {
                    echo ": heartbeat\n\n";
                    flush();
                }

                $count = DB::table('notifications')
                    ->where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', $userId)
                    ->whereNull('read_at')
                    ->count();

                if ($count !== $lastCount) {
                    $lastCount = $count;
                    echo "event: unread\n";
                    echo "data: " . json_encode(['count' => $count]) . "\n\n";
                    flush();
                }

                if (connection_aborted()) {
                    break;
                }

                sleep(5);
            }
        }, 200, [
            'Content-Type'  => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
