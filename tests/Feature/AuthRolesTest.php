<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Tests authorization behavior.
 *
 * Note: The app uses SSO-only (Azure/Microsoft OAuth), so there are no
 * username/password login endpoints. These tests verify that:
 *  - Authenticated users get the expected responses
 *  - Role-restricted endpoints enforce their roles correctly
 */
class AuthRolesTest extends TestCase
{
    use DatabaseTransactions;

    // ── Test 1: Utilisateur authentifié accède à /api/me ─────────────────────

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = $this->createUser('etudiant', '2IA', '2A');

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => ['id']]);
    }

    // ── Test 2: Unauthenticated request is rejected ───────────────────────────

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    // ── Test 3: SuperAdmin peut accéder aux endpoints admin ──────────────────

    public function test_superadmin_can_access_admin_drive_endpoints(): void
    {
        $admin = $this->createUser('superAdmin');

        $response = $this->actingAs($admin)->getJson('/api/drive/filieres');

        $response->assertStatus(200)
                 ->assertJsonStructure(['filieres']);
    }

    // ── Test 4: Étudiant ne peut pas accéder aux endpoints admin drive ────────

    public function test_etudiant_cannot_access_admin_drive_endpoints(): void
    {
        $etudiant = $this->createUser('etudiant', '2IA', '2A');

        $response = $this->actingAs($etudiant)->getJson('/api/drive/filieres');

        $response->assertStatus(403);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createUser(string $role, ?string $filiere = null, ?string $annee = null): User
    {
        return User::create([
            'emailInstitutionnel' => 'auth_test_' . uniqid() . '@ensias.ma',
            'nom'                 => 'Auth',
            'prenom'              => 'Tester',
            'roles'               => [$role],
            'filiere'             => $filiere,
            'annee'               => $annee,
        ]);
    }
}
