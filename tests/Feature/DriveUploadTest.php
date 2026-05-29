<?php

namespace Tests\Feature;

use App\Models\Annee;
use App\Models\ElementModule;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DriveUploadTest extends TestCase
{
    use DatabaseTransactions;

    private ElementModule $element;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('azure');

        $filiere = Filiere::create([
            'nom'             => 'UPLOAD_TEST_' . uniqid(),
            'slug'            => 'upload-test-' . uniqid(),
            'code'            => 'UT',
            'is_active'       => true,
            'is_tronc_commun' => false,
        ]);

        $annee = Annee::firstOrCreate(['label' => '2A'], ['niveau' => 2]);

        $module = Module::create([
            'filiere_id' => $filiere->id,
            'annee_id'   => $annee->id,
            'nom'        => 'Upload Test Module',
            'slug'       => 'upload-test-module-' . uniqid(),
            'semestre'   => 'S3',
            'is_active'  => true,
        ]);

        $this->element = ElementModule::create([
            'module_id' => $module->id,
            'nom'       => 'Upload Test Element',
            'slug'      => 'upload-test-element-' . uniqid(),
        ]);
    }

    // ── Test 1: Scolarité peut uploader un document ───────────────────────────

    public function test_scolarite_can_upload_document(): void
    {
        $scolarite = $this->createUser('scolarite');

        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $response = $this->actingAs($scolarite)
            ->postJson('/api/drive/documents', [
                'file'              => $file,
                'titre'             => 'Test Upload Document',
                'element_module_id' => $this->element->id,
                'type'              => 'Cours',
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('document.titre', 'Test Upload Document');
    }

    // ── Test 2: Étudiant ne peut pas uploader ────────────────────────────────

    public function test_etudiant_cannot_upload_document(): void
    {
        $etudiant = $this->createUser('etudiant', 'TEST', '2A');

        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $response = $this->actingAs($etudiant)
            ->postJson('/api/drive/documents', [
                'file'              => $file,
                'titre'             => 'Unauthorized Upload',
                'element_module_id' => $this->element->id,
                'type'              => 'Cours',
            ]);

        $response->assertStatus(403);
    }

    // ── Test 3: Fichier trop lourd est rejeté ────────────────────────────────

    public function test_oversized_file_is_rejected(): void
    {
        $scolarite = $this->createUser('scolarite');

        // 110 MB — over the 100 MB (102400 KB) limit
        $file = UploadedFile::fake()->create('big.pdf', 110 * 1024, 'application/pdf');

        $response = $this->actingAs($scolarite)
            ->postJson('/api/drive/documents', [
                'file'              => $file,
                'titre'             => 'Big File',
                'element_module_id' => $this->element->id,
                'type'              => 'Cours',
            ]);

        $response->assertStatus(422);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createUser(string $role, ?string $filiere = null, ?string $annee = null): User
    {
        return User::create([
            'emailInstitutionnel' => 'upload_test_' . uniqid() . '@ensias.ma',
            'nom'                 => 'Upload',
            'prenom'              => 'Tester',
            'roles'               => [$role],
            'filiere'             => $filiere,
            'annee'               => $annee,
        ]);
    }
}
