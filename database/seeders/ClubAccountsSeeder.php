<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClubAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = [
            [
                'nom'      => 'Club Bridge',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.bridge@um5.ac.ma',
                'password' => 'Bridge@ENSIAS2025',
                'photo'    => '/groups/clubs/club-bridge.jpg',
                'bio'      => 'Club Bridge ENSIAS',
            ],
            [
                'nom'      => 'Club CINDH',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.cindh@um5.ac.ma',
                'password' => 'Cindh@ENSIAS2025',
                'photo'    => '/groups/clubs/club-cindh.jpg',
                'bio'      => 'Club CINDH ENSIAS',
            ],
            [
                'nom'      => 'Club EITC',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.eitc@um5.ac.ma',
                'password' => 'Eitc@ENSIAS2025',
                'photo'    => '/groups/clubs/club-eitc.jpg',
                'bio'      => 'Club EITC ENSIAS',
            ],
            [
                'nom'      => 'Club ENSIAS Founders',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.founders@um5.ac.ma',
                'password' => 'Founders@ENSIAS2025',
                'photo'    => '/groups/clubs/club-ensias-founders.jpg',
                'bio'      => 'Club ENSIAS Founders',
            ],
            [
                'nom'      => 'Club Fintech',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.fintech@um5.ac.ma',
                'password' => 'Fintech@ENSIAS2025',
                'photo'    => '/groups/clubs/club-fintech.jpg',
                'bio'      => 'Club Fintech ENSIAS',
            ],
            [
                'nom'      => 'Club Forum GENIE-Entreprises',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.forum.genie@um5.ac.ma',
                'password' => 'Forum@ENSIAS2025',
                'photo'    => '/groups/clubs/club-forum-genie.jpg',
                'bio'      => 'Club Forum GENIE-Entreprises ENSIAS',
            ],
            [
                'nom'      => 'Club House of Art',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.houseart@um5.ac.ma',
                'password' => 'HouseArt@ENSIAS2025',
                'photo'    => '/groups/clubs/club-house-of-art.jpg',
                'bio'      => 'Club House of Art ENSIAS',
            ],
            [
                'nom'      => 'Club INSEC',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.insec@um5.ac.ma',
                'password' => 'Insec@ENSIAS2025',
                'photo'    => '/groups/clubs/club-insec.jpg',
                'bio'      => 'Club INSEC ENSIAS',
            ],
            [
                'nom'      => 'Club Japonnais',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.japonnais@um5.ac.ma',
                'password' => 'Japonnais@ENSIAS2025',
                'photo'    => '/groups/clubs/club-japonnais.jpg',
                'bio'      => 'Club Japonnais ENSIAS',
            ],
            [
                'nom'      => 'Club JEE',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.jee@um5.ac.ma',
                'password' => 'Jee@ENSIAS2025',
                'photo'    => '/groups/clubs/club-jee.jpg',
                'bio'      => 'Club Junior Entreprise ENSIAS',
            ],
            [
                'nom'      => 'Club Neurodynamics',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.neurodynamics@um5.ac.ma',
                'password' => 'Neuro@ENSIAS2025',
                'photo'    => '/groups/clubs/club-neurodynamics.jpg',
                'bio'      => 'Club Neurodynamics ENSIAS',
            ],
            [
                'nom'      => 'Club Quraan',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.quraan@um5.ac.ma',
                'password' => 'Quraan@ENSIAS2025',
                'photo'    => '/groups/clubs/club-quraan.jpg',
                'bio'      => 'Club Quraan ENSIAS',
            ],
            [
                'nom'      => 'Club Sportif',
                'prenom'   => 'ENSIAS',
                'email'    => 'club.sportif@um5.ac.ma',
                'password' => 'Sportif@ENSIAS2025',
                'photo'    => '/groups/clubs/club-sportif.jpg',
                'bio'      => 'Club Sportif ENSIAS',
            ],
        ];

        foreach ($clubs as $club) {
            $exists = DB::table('users')
                ->where('emailInstitutionnel', $club['email'])
                ->exists();

            if ($exists) {
                $this->command->warn("Déjà existant : {$club['nom']}");
                continue;
            }

            DB::table('users')->insert([
                'id'                  => Str::uuid()->toString(),
                'emailInstitutionnel' => $club['email'],
                'nom'                 => $club['nom'],
                'prenom'              => $club['prenom'],
                'password'            => Hash::make($club['password']),
                'photoProfil'         => $club['photo'],
                'bio'                 => $club['bio'],
                'profileActif'        => true,
                'roles'               => json_encode(['club']),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            $this->command->info("Créé : {$club['nom']} ({$club['email']})");
        }

        $this->command->newLine();
        $this->command->info('===== CREDENTIALS DES CLUBS =====');
        foreach ($clubs as $club) {
            $this->command->line("{$club['nom']}");
            $this->command->line("  Email    : {$club['email']}");
            $this->command->line("  Password : {$club['password']}");
            $this->command->newLine();
        }
    }
}
