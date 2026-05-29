<?php

namespace Tests\Feature;

use App\Models\Annee;
use App\Models\Document;
use App\Models\ElementModule;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DriveAccessTest extends TestCase
{
    use DatabaseTransactions;

    private Filiere $filiere;
    private Annee   $annee;
    private Module  $module;
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test filière
        $this->filiere = Filiere::create([
            'nom'             => 'TEST_FILIERE_' . uniqid(),
            'slug'            => 'test-filiere-' . uniqid(),
            'code'            => 'TF',
            'is_active'       => true,
            'is_tronc_commun' => false,
        ]);

        // Create a test year
        $this->annee = Annee::firstOrCreate(
            ['label' => '2A'],
            ['niveau' => 2]
        );

        // Create a test module
        $this->module = Module::create([
            'filiere_id' => $this->filiere->id,
            'annee_id'   => $this->annee->id,
            'nom'        => 'Test Module',
            'slug'       => 'test-module-' . uniqid(),
            'semestre'   => 'S3',
            'is_active'  => true,
        ]);

        // Create element + document
        $element = ElementModule::create([
            'module_id' => $this->module->id,
            'nom'       => 'Test Element',
            'slug'      => 'test-element-' . uniqid(),
        ]);

        $this->document = Document::create([
            'element_module_id' => $element->id,
            'titre'             => 'Test Document',
            'nom'               => 'test.pdf',
            'extension'         => 'pdf',
            'typeDocument'      => 'Cours',
            'statutValidation'  => 'Valide',
            'azure_path'        => 'TEST/2A/test-module/test-element/Cours/test.pdf',
            'azure_url'         => 'https://example.blob.core.windows.net/test.pdf',
            'downloads_count'   => 0,
            'views_count'       => 0,
        ]);
    }

    // ── Test 1: Étudiant accède à sa propre filière ───────────────────────────

    public function test_etudiant_can_access_own_filiere_documents(): void
    {
        $etudiant = $this->createUser('etudiant', $this->filiere->nom, '2A');

        $response = $this->actingAs($etudiant)
            ->getJson('/api/drive/documents/' . $this->document->id . '/view');

        // Should succeed (200) — student can access their own filiere's documents
        $response->assertStatus(200);
    }

    // ── Test 2: Étudiant ne peut pas accéder à une autre filière ─────────────

    public function test_etudiant_cannot_access_other_filiere_documents(): void
    {
        $otherFiliere = Filiere::create([
            'nom'             => 'OTHER_FILIERE_' . uniqid(),
            'slug'            => 'other-filiere-' . uniqid(),
            'code'            => 'OF',
            'is_active'       => true,
            'is_tronc_commun' => false,
        ]);

        // Student belongs to a DIFFERENT filiere
        $etudiant = $this->createUser('etudiant', $otherFiliere->nom, '2A');

        $response = $this->actingAs($etudiant)
            ->getJson('/api/drive/documents/' . $this->document->id . '/view');

        $response->assertStatus(403);
    }

    // ── Test 3: Superadmin peut accéder à tous les documents ─────────────────

    public function test_superadmin_can_access_any_document(): void
    {
        $admin = $this->createUser('superAdmin');

        $response = $this->actingAs($admin)
            ->getJson('/api/drive/documents/' . $this->document->id . '/view');

        $response->assertStatus(200);
    }

    // ── Test 4: Étudiant avec profil incomplet est refusé ────────────────────

    public function test_etudiant_with_incomplete_profile_is_blocked(): void
    {
        // Student with NO filiere/annee set
        $etudiant = $this->createUser('etudiant', null, null);

        $response = $this->actingAs($etudiant)
            ->getJson('/api/drive/mes-modules');

        $response->assertStatus(403)
                 ->assertJsonFragment(['error' => 'PROFILE_INCOMPLETE']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createUser(string $role, ?string $filiere = null, ?string $annee = null): User
    {
        return User::create([
            'emailInstitutionnel' => 'test_' . uniqid() . '@ensias.ma',
            'nom'                 => 'Test',
            'prenom'              => 'User',
            'roles'               => [$role],
            'filiere'             => $filiere,
            'annee'               => $annee,
        ]);
    }
}
