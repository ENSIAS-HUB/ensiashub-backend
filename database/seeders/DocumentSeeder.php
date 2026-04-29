<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            [
                'id' => '11111111-1111-1111-1111-ffffffffffff',
                'titre' => 'Cours Algorithmique Avancée',
                'nom' => 'cours_algorithmique.pdf',
                'format' => 'pdf',
                'urlStockage' => 'documents/cours_algorithmique.pdf',
                'typeDocument' => 'Cours',
                'statutValidation' => 'Valide',
                'uploader_id' => '33333333-3333-3333-3333-333333333333',
                'module_pedagogique_id' => '11111111-1111-1111-1111-eeeeeeeeeeee',
                'publishedAt' => now(),
                'downloads_count' => 0,
            ],
            [
                'id' => '22222222-2222-2222-2222-ffffffffffff',
                'titre' => 'TD Base de Données',
                'nom' => 'td_bdd.pdf',
                'format' => 'pdf',
                'urlStockage' => 'documents/td_bdd.pdf',
                'typeDocument' => 'TD',
                'statutValidation' => 'Valide',
                'uploader_id' => '44444444-4444-4444-4444-444444444444',
                'module_pedagogique_id' => '22222222-2222-2222-2222-eeeeeeeeeeee',
                'publishedAt' => now(),
                'downloads_count' => 0,
            ],
            [
                'id' => '33333333-3333-3333-3333-ffffffffffff',
                'titre' => 'Examen Génie Logiciel 2025',
                'nom' => 'examen_gl_2025.pdf',
                'format' => 'pdf',
                'urlStockage' => 'documents/examen_gl_2025.pdf',
                'typeDocument' => 'Examen',
                'statutValidation' => 'EnAttente',
                'uploader_id' => '55555555-5555-5555-5555-555555555555',
                'module_pedagogique_id' => '33333333-3333-3333-3333-eeeeeeeeeeee',
                'publishedAt' => null,
                'downloads_count' => 0,
            ],
            [
                'id' => '44444444-4444-4444-4444-ffffffffffff',
                'titre' => 'Support Réseaux',
                'nom' => 'support_reseaux.pptx',
                'format' => 'pptx',
                'urlStockage' => 'documents/support_reseaux.pptx',
                'typeDocument' => 'Cours',
                'statutValidation' => 'Valide',
                'uploader_id' => '66666666-6666-6666-6666-666666666666',
                'module_pedagogique_id' => '44444444-4444-4444-4444-eeeeeeeeeeee',
                'publishedAt' => now(),
                'downloads_count' => 0,
            ],
        ];

        foreach ($documents as $document) {
            Document::create($document);
        }
    }
}