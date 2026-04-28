<?php

namespace Database\Seeders;

use App\Models\AdhesionGroup;
use Illuminate\Database\Seeder;

class AdhesionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $adhesions = [
            // Groupe Génie Informatique
            [
                'id' => '11111111-aaaa-1111-aaaa-111111111111',
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'group_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '11111111-bbbb-1111-bbbb-111111111111',
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'group_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'statut' => 'Approuve',
                'role' => 'Moderateur',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '11111111-cccc-1111-cccc-111111111111',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'group_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            // Groupe Gaming Club
            [
                'id' => '22222222-aaaa-2222-aaaa-222222222222',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'group_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '22222222-bbbb-2222-bbbb-222222222222',
                'user_id' => '55555555-5555-5555-5555-555555555555',
                'group_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'statut' => 'EnAttente',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => null,
            ],
            // Groupe Général
            [
                'id' => '33333333-aaaa-3333-aaaa-333333333333',
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'group_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '33333333-bbbb-3333-bbbb-333333333333',
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'group_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'statut' => 'Approuve',
                'role' => 'Moderateur',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '33333333-cccc-3333-cccc-333333333333',
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'group_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '33333333-dddd-3333-dddd-333333333333',
                'user_id' => '55555555-5555-5555-5555-555555555555',
                'group_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
            [
                'id' => '33333333-eeee-3333-eeee-333333333333',
                'user_id' => '66666666-6666-6666-6666-666666666666',
                'group_id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'statut' => 'Approuve',
                'role' => 'Membre',
                'joinedAt' => now(),
                'reviewedAt' => now(),
            ],
        ];

        foreach ($adhesions as $adhesion) {
            AdhesionGroup::create($adhesion);
        }
    }
}