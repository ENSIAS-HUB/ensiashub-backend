<?php

namespace Database\Seeders;

use App\Models\Reaction;
use App\Models\Interaction;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $reactions = [
            [
                'interaction_id' => '44444444-4444-4444-4444-cccccccccccc',
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'publication_id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'reaction' => 'like',
            ],
            [
                'interaction_id' => '55555555-5555-5555-5555-cccccccccccc',
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'publication_id' => '22222222-2222-2222-2222-aaaaaaaaaaaa',
                'reaction' => 'love',
            ],
            [
                'interaction_id' => '66666666-6666-6666-6666-cccccccccccc',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'publication_id' => '33333333-3333-3333-3333-aaaaaaaaaaaa',
                'reaction' => 'laugh',
            ],
            [
                'interaction_id' => '77777777-7777-7777-7777-cccccccccccc',
                'user_id' => '55555555-5555-5555-5555-555555555555',
                'publication_id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'reaction' => 'like',
            ],
        ];

        foreach ($reactions as $data) {
            // Créer l'interaction parente
            $interaction = Interaction::create([
                'id' => $data['interaction_id'],
                'user_id' => $data['user_id'],
                'publication_id' => $data['publication_id'],
                'type' => 'reaction',
            ]);

            // Créer la réaction liée
            Reaction::create([
                'id' => $interaction->id,
                'reaction' => $data['reaction'],
            ]);
        }
    }
}