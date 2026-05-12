<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupSeeder extends Seeder
{
    private array $filieres = ['GL', 'GD', 'D2S', '2IA', '2SCL', 'SSE', 'CSCMC', 'IDF', 'BI&A'];
    private array $annees   = ['1A', '2A', '3A'];

    private array $clubs = [
        ['nom' => 'EITC',                        'description' => 'Club de technologie et d\'innovation de l\'ENSIAS.'],
        ['nom' => 'INSEC',                        'description' => 'Club axé sur la sécurité informatique et la cybersécurité.'],
        ['nom' => 'Club Quraan',                  'description' => 'Club dédié à l\'apprentissage et la récitation du Saint Coran.'],
        ['nom' => 'Club Bridge',                  'description' => 'Club de jeu de cartes Bridge pour développer la stratégie et la logique.'],
        ['nom' => 'Club Japonnais',               'description' => 'Club de culture et de langue japonaise de l\'ENSIAS.'],
        ['nom' => 'Club Neurodynamics',           'description' => 'Club de neurosciences et de technologies cognitives.'],
        ['nom' => 'Club Forum GENIE-Entreprises', 'description' => 'Forum annuel entre les ingénieurs de l\'ENSIAS et le monde de l\'entreprise.'],
        ['nom' => 'Club CINDH',                   'description' => 'Club d\'initiative et de développement humain de l\'ENSIAS.'],
        ['nom' => 'Club JEE',                     'description' => 'Junior Entreprise d\'ENSIAS — accompagne les projets entrepreneuriaux.'],
        ['nom' => 'Club Sportif',                 'description' => 'Club multi-sports : football, basketball, tennis de table et plus.'],
        ['nom' => 'Club Fintech',                 'description' => 'Club dédié à la finance technologique et aux innovations financières.'],
        ['nom' => 'Club House of Art',            'description' => 'Club artistique : musique, peinture, photographie et arts numériques.'],
        ['nom' => 'Club ENSIAS Founders',         'description' => 'Club des entrepreneurs : startups et projets innovants des étudiants.'],
    ];

    public function run(): void
    {
        // ── Purge toutes les anciennes données (anciens faux groupes) ─────
        DB::statement('TRUNCATE TABLE adhesion_groups CASCADE');
        DB::statement('TRUNCATE TABLE groups CASCADE');

        $admin = User::whereRaw('LOWER("emailInstitutionnel") = ?', ['yassermoulaysm@gmail.com'])->first()
                 ?? User::first();

        if (!$admin) {
            $this->command->error('No users found. Run AllowedEmailSeeder first.');
            return;
        }

        // ── 1. Groupes de filières (27 groupes) ──────────────────────────
        foreach ($this->filieres as $filiere) {
            foreach ($this->annees as $annee) {
                Group::create([
                    'nom'         => "{$filiere} {$annee}",
                    'categorie'   => 'Filiere',
                    'description' => "Groupe officiel des étudiants de {$filiere} – {$annee} de l'ENSIAS.",
                    'createur_id' => $admin->id,
                    'creeLe'      => now(),
                ]);
            }
        }

        // ── 2. Clubs parascolaires (13 groupes) ──────────────────────────
        foreach ($this->clubs as $club) {
            Group::create([
                'nom'         => $club['nom'],
                'categorie'   => 'Club',
                'description' => $club['description'],
                'createur_id' => $admin->id,
                'creeLe'      => now(),
            ]);
        }

        // ── 3. Groupe général ─────────────────────────────────────────────
        Group::create([
            'nom'         => 'Général ENSIAS',
            'categorie'   => 'General',
            'description' => 'Groupe général pour tous les étudiants de l\'ENSIAS.',
            'createur_id' => $admin->id,
            'creeLe'      => now(),
        ]);

        $total = (count($this->filieres) * count($this->annees)) + count($this->clubs) + 1;
        $this->command->info("GroupSeeder: {$total} groupes créés.");
    }
}

