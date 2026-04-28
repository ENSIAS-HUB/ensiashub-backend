<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'emailInstitutionnel' => 'admin@ensias.ma',
            'nom' => 'Admin',
            'prenom' => 'System',
            'password' => Hash::make('password'),
            'photoProfil' => null,
            'bio' => 'Administrateur de la plateforme',
            'profileActif' => true,
            'roles' => ['admin', 'moderateur'],
        ]);

        // Étudiants
        $etudiants = [
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'emailInstitutionnel' => 'ahmed.benali@ensias.ma',
                'nom' => 'Benali',
                'prenom' => 'Ahmed',
                'bio' => 'Étudiant en 3ème année Génie Informatique',
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'emailInstitutionnel' => 'fatima.zahra@ensias.ma',
                'nom' => 'Zahra',
                'prenom' => 'Fatima',
                'bio' => 'Étudiante en 2ème année Réseaux',
            ],
            [
                'id' => '44444444-4444-4444-4444-444444444444',
                'emailInstitutionnel' => 'youssef.alami@ensias.ma',
                'nom' => 'Alami',
                'prenom' => 'Youssef',
                'bio' => 'Étudiant en 1ère année Cycle Ingénieur',
            ],
            [
                'id' => '55555555-5555-5555-5555-555555555555',
                'emailInstitutionnel' => 'sara.meknes@ensias.ma',
                'nom' => 'Meknes',
                'prenom' => 'Sara',
                'bio' => 'Étudiante en 3ème année Génie Logiciel',
            ],
            [
                'id' => '66666666-6666-6666-6666-666666666666',
                'emailInstitutionnel' => 'karim.saidi@ensias.ma',
                'nom' => 'Saidi',
                'prenom' => 'Karim',
                'bio' => 'Étudiant en 2ème année Cloud Computing',
            ],
        ];

        foreach ($etudiants as $data) {
            User::create(array_merge($data, [
                'password' => Hash::make('password'),
                'photoProfil' => null,
                'profileActif' => true,
                'roles' => ['etudiant'],
            ]));
        }
    }
}