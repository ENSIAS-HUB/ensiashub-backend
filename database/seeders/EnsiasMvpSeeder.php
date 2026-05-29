<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EnsiasMvpSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'emailInstitutionnel' => 'yasser@ensias.ma',
                'nom'                 => 'Admin',
                'prenom'              => 'Yasser',
                'username'            => 'yasser',
                'roles'               => ['superAdmin'],
                'filiere'             => null,
                'annee'               => null,
            ],
            [
                'emailInstitutionnel' => 'scolarite@ensias.ma',
                'nom'                 => 'Scolarité',
                'prenom'              => 'ENSIAS',
                'username'            => 'scolarite',
                'roles'               => ['chef_scolarite'],
                'filiere'             => null,
                'annee'               => null,
            ],
            [
                'emailInstitutionnel' => 'etudiant1a@ensias.ma',
                'nom'                 => 'Etudiant',
                'prenom'              => 'PremièreAnnée',
                'username'            => 'etudiant1a',
                'roles'               => ['etudiant'],
                'filiere'             => 'tronc-commun',
                'annee'               => '1A',
            ],
            [
                'emailInstitutionnel' => 'etudiantgl@ensias.ma',
                'nom'                 => 'Etudiant',
                'prenom'              => 'GenieLogiciel',
                'username'            => 'etudiantgl',
                'roles'               => ['etudiant'],
                'filiere'             => 'gl',
                'annee'               => '2A',
            ],
            [
                'emailInstitutionnel' => 'etudiantd2s@ensias.ma',
                'nom'                 => 'Etudiant',
                'prenom'              => 'D2S',
                'username'            => 'etudiantd2s',
                'roles'               => ['etudiant'],
                'filiere'             => 'd2s',
                'annee'               => '3A',
            ],
            [
                'emailInstitutionnel' => 'cuisine@ensias.ma',
                'nom'                 => 'Cuisine',
                'prenom'              => 'ENSIAS',
                'username'            => 'cuisine',
                'roles'               => ['cuisinier'],
                'filiere'             => null,
                'annee'               => null,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['emailInstitutionnel' => $data['emailInstitutionnel']],
                array_merge($data, [
                    'password'      => Hash::make('password123'),
                    'profileActif'  => true,
                    'provider'      => 'local',
                ])
            );
        }

        $this->command->info('EnsiasMvpSeeder: 6 users seeded successfully.');
    }
}
