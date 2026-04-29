<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
                'nom' => 'Génie logiciele 2028',
                'categorie' => 'Filiere',
                'description' => 'Groupe officiel des étudiants de Génie Logiciele promotion 2028',
                'createur_id' => '11111111-1111-1111-1111-111111111111',
                'creeLe' => now(),
            ],
            [
                'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
                'nom' => 'ENSIAS Gaming Club',
                'categorie' => 'Club',
                'description' => 'Club de gaming et e-sport de l\'ENSIAS',
                'createur_id' => '22222222-2222-2222-2222-222222222222',
                'creeLe' => now(),
            ],
            [
                'id' => 'cccccccc-cccc-cccc-cccc-cccccccccccc',
                'nom' => 'Général ENSIAS',
                'categorie' => 'General',
                'description' => 'Groupe général pour tous les étudiants de l\'ENSIAS',
                'createur_id' => '11111111-1111-1111-1111-111111111111',
                'creeLe' => now(),
            ],
            [
                'id' => 'dddddddd-dddd-dddd-dddd-dddddddddddd',
                'nom' => 'Club Robotique ENSIAS',
                'categorie' => 'Club',
                'description' => 'Club de robotique et innovation',
                'createur_id' => '33333333-3333-3333-3333-333333333333',
                'creeLe' => now(),
            ],
            [
                'id' => 'eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee',
                'nom' => 'Réseaux & Sécurité 2025',
                'categorie' => 'Filiere',
                'description' => 'Groupe des étudiants en Réseaux et Sécurité',
                'createur_id' => '44444444-4444-4444-4444-444444444444',
                'creeLe' => now(),
            ],
        ];

        foreach ($groups as $group) {
            Group::create($group);
        }
    }
}