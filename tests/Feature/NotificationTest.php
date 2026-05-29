<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    // ── Test 1: Utilisateur reçoit une notification ───────────────────────────

    public function test_user_receives_notification(): void
    {
        $sender    = $this->createUser('etudiant');
        $recipient = $this->createUser('etudiant');

        // Act as the sender
        $this->actingAs($sender);

        /** @var NotificationService $svc */
        $svc = app(NotificationService::class);
        $svc->send($recipient, 'test', 'Titre test', 'Corps de la notification');

        // Recipient should see it
        $response = $this->actingAs($recipient)->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonPath('data.0.title', 'Titre test');
    }

    // ── Test 2: Le compteur de non-lus est correct ────────────────────────────

    public function test_unread_count_is_correct(): void
    {
        $sender    = $this->createUser('etudiant');
        $recipient = $this->createUser('etudiant');

        $this->actingAs($sender);

        /** @var NotificationService $svc */
        $svc = app(NotificationService::class);
        $svc->send($recipient, 'test', 'Notif 1', 'Corps 1');
        $svc->send($recipient, 'test', 'Notif 2', 'Corps 2');

        $response = $this->actingAs($recipient)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                 ->assertJsonPath('count', 2);
    }

    // ── Test 3: Marquer tout comme lu ramène le compteur à 0 ─────────────────

    public function test_mark_all_read_resets_count_to_zero(): void
    {
        $sender    = $this->createUser('etudiant');
        $recipient = $this->createUser('etudiant');

        $this->actingAs($sender);

        /** @var NotificationService $svc */
        $svc = app(NotificationService::class);
        $svc->send($recipient, 'test', 'Notif', 'Corps');

        // Mark all as read
        $this->actingAs($recipient)->postJson('/api/notifications/read-all');

        $response = $this->actingAs($recipient)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
                 ->assertJsonPath('count', 0);
    }

    // ── Test 4: Pas de notification à soi-même ───────────────────────────────

    public function test_no_self_notification(): void
    {
        $user = $this->createUser('etudiant');

        // Act as the user AND send to themselves
        $this->actingAs($user);

        /** @var NotificationService $svc */
        $svc = app(NotificationService::class);
        $svc->send($user, 'test', 'Self Notif', 'Should not appear');

        $response = $this->actingAs($user)
            ->getJson('/api/notifications/unread-count');

        // Count should be 0 — self-notifications are skipped
        $response->assertStatus(200)
                 ->assertJsonPath('count', 0);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createUser(string $role): User
    {
        return User::create([
            'emailInstitutionnel' => 'notif_test_' . uniqid() . '@ensias.ma',
            'nom'                 => 'Notif',
            'prenom'              => 'Tester',
            'roles'               => [$role],
        ]);
    }
}
