<?php

namespace Database\Seeders;

use App\Models\DocumentReview;
use Illuminate\Database\Seeder;

class DocumentReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'id' => '11111111-1111-1111-1111-gggggggggggg',
                'document_id' => '11111111-1111-1111-1111-ffffffffffff',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'Document conforme',
            ],
            [
                'id' => '22222222-2222-2222-2222-gggggggggggg',
                'document_id' => '22222222-2222-2222-2222-ffffffffffff',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'TD validé',
            ],
            [
                'id' => '33333333-3333-3333-3333-gggggggggggg',
                'document_id' => '33333333-3333-3333-3333-ffffffffffff',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'EnAttente',
                'reviewedAt' => now(),
                'motif' => null,
            ],
            [
                'id' => '44444444-4444-4444-4444-gggggggggggg',
                'document_id' => '44444444-4444-4444-4444-ffffffffffff',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'Support validé',
            ],
        ];

        foreach ($reviews as $review) {
            DocumentReview::create($review);
        }
    }
}