<?php

namespace Database\Seeders;

use App\Models\Publication;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        $publications = [
            [
                'id' => '11111111-1111-1111-1111-aaaaaaaaaaaa',
                'contenu' => 'Bienvenue à tous les étudiants de Génie Informatique 2026 ! N\'hésitez pas à poser vos questions ici.',
                'typeMedia' => null,
                'statutValidation' => 'Valide',
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'groupe_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'publishedAt' => now(),
            ],
            [
                'id' => '22222222-2222-2222-2222-aaaaaaaaaaaa',
                'contenu' => 'Quelqu\'un a des ressources pour le module Algorithmique Avancée ?',
                'typeMedia' => null,
                'statutValidation' => 'Valide',
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'groupe_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'publishedAt' => now(),
            ],
            [
                'id' => '33333333-3333-3333-3333-aaaaaaaaaaaa',
                'contenu' => 'Tournoi de League of Legends ce vendredi à 18h ! Inscrivez-vous ici.',
                'typeMedia' => 'image',
                'statutValidation' => 'Valide',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'groupe_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'publishedAt' => now(),
            ],
            [
                'id' => '44444444-4444-4444-4444-aaaaaaaaaaaa',
                'contenu' => 'Nouveau cours de Réseaux disponible dans le drive !',
                'typeMedia' => 'lien',
                'statutValidation' => 'EnAttente',
                'user_id' => '55555555-5555-5555-5555-555555555555',
                'groupe_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'publishedAt' => null,
            ],
            [
                'id' => '55555555-5555-5555-5555-aaaaaaaaaaaa',
                'contenu' => 'Bienvenue à tous les nouveaux étudiants !',
                'typeMedia' => null,
                'statutValidation' => 'Valide',
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'groupe_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'publishedAt' => now(),
            ],
        ];

        foreach ($publications as $publication) {
            Publication::create($publication);
        }
    }
}