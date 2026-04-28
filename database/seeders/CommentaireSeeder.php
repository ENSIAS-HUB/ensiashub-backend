<?php

namespace Database\Seeders;

use App\Models\Commentaire;
use App\Models\Interaction;
use Illuminate\Database\Seeder;

class CommentaireSeeder extends Seeder
{
    public function run(): void
    {
        $commentaires = [
            [
                'interaction_id' => '11111111-1111-1111-1111-cccccccccccc',
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'publication_id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'contenu' => 'Super publication ! Merci du partage.',
            ],
            [
                'interaction_id' => '22222222-2222-2222-2222-cccccccccccc',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'publication_id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'contenu' => 'Très intéressant, merci !',
            ],
            [
                'interaction_id' => '33333333-3333-3333-3333-cccccccccccc',
                'user_id' => '55555555-5555-5555-5555-555555555555',
                'publication_id' => '22222222-2222-2222-2222-aaaaaaaaaaaa',
                'contenu' => 'J\'ai besoin d\'aide aussi !',
            ],
        ];

        foreach ($commentaires as $data) {
            // Créer l'interaction parente
            $interaction = Interaction::create([
                'id' => $data['interaction_id'],
                'user_id' => $data['user_id'],
                'publication_id' => $data['publication_id'],
                'type' => 'commentaire',
            ]);

            // Créer le commentaire lié
            Commentaire::create([
                'id' => $interaction->id,
                'contenu' => $data['contenu'],
            ]);
        }
    }
}