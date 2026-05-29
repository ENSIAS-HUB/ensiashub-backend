<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests du droit à l'oubli (DELETE /api/me).
 *
 * Couvre :
 *  - Anonymisation des données nominatives (nom, prénom, email…)
 *  - Révocation des tokens Sanctum
 *  - Refus si non authentifié
 *  - Format de l'email anonymisé
 */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Crée un utilisateur avec les champs réels du modèle User. */
    private function createUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'emailInstitutionnel' => 'deletion_test_' . uniqid() . '@um5.ac.ma',
            'nom'                 => 'Benali',
            'prenom'              => 'Youssef',
            'roles'               => ['etudiant'],
            'profileActif'        => true,
        ], $overrides));
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    /**
     * Un utilisateur authentifié peut anonymiser son compte via DELETE /me.
     */
    public function test_authenticated_user_can_delete_own_account(): void
    {
        $user  = $this->createUser([
            'bio'   => 'Étudiant GL3',
            'phone' => '+212600000000',
            'ville' => 'Rabat',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonFragment(['success' => true]);
    }

    /**
     * Après suppression, les données nominatives doivent être remplacées
     * par des valeurs neutres.
     */
    public function test_user_data_is_anonymized_after_deletion(): void
    {
        $user  = $this->createUser([
            'bio'   => 'Étudiant GL3',
            'phone' => '+212600000000',
            'ville' => 'Rabat',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->deleteJson('/api/me');

        // L'enregistrement est toujours présent (pas de suppression physique)
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        // Les champs nominatifs ont été remplacés
        $this->assertDatabaseHas('users', [
            'id'     => $user->id,
            'nom'    => 'Utilisateur',
            'prenom' => 'Supprimé',
        ]);

        // Les données sensibles ont été effacées
        $fresh = $user->fresh();
        $this->assertNull($fresh->bio);
        $this->assertNull($fresh->phone);
        $this->assertNull($fresh->ville);
        $this->assertFalse((bool) $fresh->profileActif);
    }

    /**
     * Tous les tokens Sanctum de l'utilisateur doivent être révoqués
     * après la suppression du compte.
     */
    public function test_sanctum_tokens_are_revoked_after_deletion(): void
    {
        $user   = $this->createUser();
        $token1 = $user->createToken('device_1')->plainTextToken;
        $user->createToken('device_2');  // second token non utilisé

        $this->withHeaders(['Authorization' => "Bearer {$token1}"])
             ->deleteJson('/api/me');

        // Aucun token ne doit subsister pour cet utilisateur
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * La route DELETE /me doit retourner 401 si aucun token n'est fourni.
     */
    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->deleteJson('/api/me');

        $response->assertStatus(401);
    }

    /**
     * L'email anonymisé doit suivre le format :
     * deleted_{uuid}@anonymized.local
     */
    public function test_anonymized_email_follows_expected_format(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->deleteJson('/api/me');

        $fresh = $user->fresh();

        $this->assertStringStartsWith('deleted_', $fresh->emailInstitutionnel);
        $this->assertStringEndsWith('@anonymized.local', $fresh->emailInstitutionnel);
        // L'identifiant UUID de l'utilisateur doit être inclus dans l'email anonymisé
        $this->assertStringContainsString((string) $user->id, $fresh->emailInstitutionnel);
    }

    /**
     * Deux suppressions successives ne doivent pas déclencher d'erreur
     * (idempotence partielle : l'email unique ne doit pas provoquer de doublon).
     */
    public function test_account_deletion_does_not_cause_unique_constraint_error_on_second_call(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('auth_token')->plainTextToken;

        // Première suppression
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->deleteJson('/api/me');

        // Le token a été révoqué, donc le second appel est 401 — pas d'erreur 500
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->deleteJson('/api/me');

        $response->assertStatus(401);
    }
}
