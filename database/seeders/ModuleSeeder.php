<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // Génie Informatique
            [
                'id' => '11111111-1111-1111-1111-eeeeeeeeeeee',
                'filiere_id' => '11111111-1111-1111-1111-dddddddddddd',
                'nom' => 'Algorithmique Avancée',
                'semestre' => 'S3',
                'annee' => 2,
            ],
            [
                'id' => '22222222-2222-2222-2222-eeeeeeeeeeee',
                'filiere_id' => '11111111-1111-1111-1111-dddddddddddd',
                'nom' => 'Base de Données',
                'semestre' => 'S3',
                'annee' => 2,
            ],
            [
                'id' => '33333333-3333-3333-3333-eeeeeeeeeeee',
                'filiere_id' => '11111111-1111-1111-1111-dddddddddddd',
                'nom' => 'Génie Logiciel',
                'semestre' => 'S4',
                'annee' => 2,
            ],
            [
                'id' => '44444444-4444-4444-4444-eeeeeeeeeeee',
                'filiere_id' => '11111111-1111-1111-1111-dddddddddddd',
                'nom' => 'Réseaux',
                'semestre' => 'S4',
                'annee' => 2,
            ],
            // Génie Réseaux et Sécurité
            [
                'id' => '55555555-5555-5555-5555-eeeeeeeeeeee',
                'filiere_id' => '22222222-2222-2222-2222-dddddddddddd',
                'nom' => 'Cryptographie',
                'semestre' => 'S5',
                'annee' => 3,
            ],
            [
                'id' => '66666666-6666-6666-6666-eeeeeeeeeeee',
                'filiere_id' => '22222222-2222-2222-2222-dddddddddddd',
                'nom' => 'Sécurité des Réseaux',
                'semestre' => 'S5',
                'annee' => 3,
            ],
            // Génie Logiciel
            [
                'id' => '77777777-7777-7777-7777-eeeeeeeeeeee',
                'filiere_id' => '33333333-3333-3333-3333-dddddddddddd',
                'nom' => 'Architecture Logicielle',
                'semestre' => 'S5',
                'annee' => 3,
            ],
            [
                'id' => '88888888-8888-8888-8888-eeeeeeeeeeee',
                'filiere_id' => '33333333-3333-3333-3333-dddddddddddd',
                'nom' => 'DevOps',
                'semestre' => 'S6',
                'annee' => 3,
            ],
        ];

        foreach ($modules as $module) {
            Module::create($module);
        }
    }
}