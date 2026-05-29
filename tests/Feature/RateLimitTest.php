<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de la limitation de débit (rate limiting).
 *
 * Couvre :
 *  - Route POST /api/auth/login  → throttle:auth-login (5 req/min par IP)
 *  - Route POST /api/drive/documents → throttle:drive-upload (10 req/h par user)
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Crée un utilisateur avec les champs réels du modèle User. */
    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'emailInstitutionnel' => 'rate_test_' . uniqid() . '@um5.ac.ma',
            'nom'                 => 'Test',
            'prenom'              => 'RateLimit',
            'roles'               => ['etudiant'],
            'profileActif'        => true,
        ], $overrides));
    }

    // ── Tests login throttle ──────────────────────────────────────────────────

    /**
     * La 6e tentative de connexion depuis la même IP doit retourner 429.
     */
    public function test_auth_login_is_rate_limited_after_5_attempts(): void
    {
        // 5 tentatives échouées depuis la même IP simulée
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', [
                'email'    => 'inexistant@um5.ac.ma',
                'password' => 'mauvais_mdp',
            ]);
        }

        // La 6e tentative doit être bloquée par le rate limiter
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'inexistant@um5.ac.ma',
            'password' => 'mauvais_mdp',
        ]);

        $response->assertStatus(429);
    }

    /**
     * Les headers X-RateLimit-* doivent être présents sur la route de connexion.
     */
    public function test_rate_limit_headers_are_present_on_login_route(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@um5.ac.ma',
            'password' => 'mauvais_mdp',
        ]);

        // Laravel injecte automatiquement ces headers pour les routes throttlées
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Un utilisateur non bloqué doit toujours passer (ne pas recevoir 429).
     */
    public function test_first_login_attempt_is_not_rate_limited(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'premier@um5.ac.ma',
            'password' => 'mauvais_mdp',
        ]);

        // Peut être 401/422 selon la logique métier, mais jamais 429 au 1er appel
        $response->assertStatus(fn (int $status) => $status !== 429);
    }

    // ── Tests Drive upload throttle ───────────────────────────────────────────

    /**
     * Le 11e upload Drive d'un utilisateur dans la même heure doit retourner 429.
     */
    public function test_drive_upload_is_rate_limited_after_10_uploads(): void
    {
        // Crée un utilisateur avec le rôle scolarité (autorisé à uploader)
        $user = $this->createUser(['roles' => ['scolarite']]);

        // 10 tentatives d'upload (échouent en 422 faute de fichier,
        // mais sont quand même comptabilisées par le rate limiter)
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user, 'sanctum')
                ->postJson('/api/drive/documents', []);
        }

        // Le 11e upload doit être refusé par le rate limiter (429)
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/drive/documents', []);

        $response->assertStatus(429);
    }

    /**
     * Les compteurs de rate limit sont isolés par utilisateur.
     * Un second utilisateur ne doit pas être bloqué par les uploads du premier.
     */
    public function test_drive_upload_rate_limit_is_per_user(): void
    {
        $userA = $this->createUser(['roles' => ['scolarite'], 'emailInstitutionnel' => 'usera_rate@um5.ac.ma']);
        $userB = $this->createUser(['roles' => ['scolarite'], 'emailInstitutionnel' => 'userb_rate@um5.ac.ma']);

        // UserA épuise son quota de 10 uploads
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($userA, 'sanctum')
                ->postJson('/api/drive/documents', []);
        }

        // UserB ne doit pas être affecté par les uploads de UserA
        $response = $this->actingAs($userB, 'sanctum')
            ->postJson('/api/drive/documents', []);

        // UserB peut être 422 (validation manquante) ou autre code métier, jamais 429
        $this->assertNotSame(429, $response->status());
    }
}
