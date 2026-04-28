<?php

namespace Database\Seeders;

use App\Models\PublicationReview;
use Illuminate\Database\Seeder;

class PublicationReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'id' => '11111111-1111-1111-1111-bbbbbbbbbbbb',
                'publication_id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'Publication conforme',
            ],
            [
                'id' => '22222222-2222-2222-2222-bbbbbbbbbbbb',
                'publication_id' => '22222222-2222-2222-2222-aaaaaaaaaaaa',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'Approuvé',
            ],
            [
                'id' => '33333333-3333-3333-3333-bbbbbbbbbbbb',
                'publication_id' => '33333333-3333-3333-3333-aaaaaaaaaaaa',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'OK',
            ],
            [
                'id' => '44444444-4444-4444-4444-bbbbbbbbbbbb',
                'publication_id' => '44444444-4444-4444-4444-aaaaaaaaaaaa',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'EnAttente',
                'reviewedAt' => now(),
                'motif' => null,
            ],
            [
                'id' => '55555555-5555-5555-5555-bbbbbbbbbbbb',
                'publication_id' => '55555555-5555-5555-5555-aaaaaaaaaaaa',
                'moderateur_id' => '11111111-1111-1111-1111-111111111111',
                'statut' => 'Valide',
                'reviewedAt' => now(),
                'motif' => 'Publication approuvée',
            ],
        ];

        foreach ($reviews as $review) {
            PublicationReview::create($review);
        }
    }
}