<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GroupSeeder extends Seeder
{
    // Filières with per-year variants
    private array $filiereAnnee = [
        ['filiere' => 'GL',   'annee' => '1A'],
        ['filiere' => 'GL',   'annee' => '2A'],
        ['filiere' => 'GL',   'annee' => '3A'],
        ['filiere' => 'GD',   'annee' => '1A'],
        ['filiere' => 'GD',   'annee' => '2A'],
        ['filiere' => 'GD',   'annee' => '3A'],
        ['filiere' => 'IDF',  'annee' => '1A'],
        ['filiere' => 'IDF',  'annee' => '2A'],
        ['filiere' => 'IDF',  'annee' => '3A'],
        ['filiere' => 'BI&A', 'annee' => '1A'],
        ['filiere' => 'BI&A', 'annee' => '2A'],
        ['filiere' => 'BI&A', 'annee' => '3A'],
        ['filiere' => 'D2S',  'annee' => '1A'],
        ['filiere' => 'D2S',  'annee' => '2A'],
        ['filiere' => 'D2S',  'annee' => '3A'],
        ['filiere' => '2IA',  'annee' => '1A'],
        ['filiere' => '2IA',  'annee' => '2A'],
        ['filiere' => '2IA',  'annee' => '3A'],
        ['filiere' => '2SCL', 'annee' => '1A'],
        ['filiere' => '2SCL', 'annee' => '2A'],
        ['filiere' => '2SCL', 'annee' => '3A'],
        ['filiere' => 'SSE',  'annee' => '1A'],
        ['filiere' => 'SSE',  'annee' => '2A'],
        ['filiere' => 'SSE',  'annee' => '3A'],
        ['filiere' => 'CSCMC','annee' => '1A'],
        ['filiere' => 'CSCMC','annee' => '2A'],
        ['filiere' => 'CSCMC','annee' => '3A'],
    ];

    // Filières without year variant (single group)
    private array $filiereOnly = [
        ['filiere' => 'EITC',  'nom' => 'EITC',  'description' => 'Groupe officiel de la filière EITC de l\'ENSIAS.'],
        ['filiere' => 'INSEC', 'nom' => 'INSEC', 'description' => 'Groupe officiel de la filière INSEC de l\'ENSIAS.'],
    ];

    private array $clubs = [
        ['nom' => 'Club Quraan',                  'slug' => 'club-quraan',              'description' => 'Club dédié à l\'apprentissage et la récitation du Saint Coran.'],
        ['nom' => 'Club Bridge',                  'slug' => 'club-bridge',              'description' => 'Club de jeu de cartes Bridge pour développer la stratégie et la logique.'],
        ['nom' => 'Club Japonnais',               'slug' => 'club-japonais',            'description' => 'Club de culture et de langue japonaise de l\'ENSIAS.'],
        ['nom' => 'Club Neurodynamics',           'slug' => 'club-neurodynamics',       'description' => 'Club de neurosciences et de technologies cognitives.'],
        ['nom' => 'Club Forum GENIE-Entreprises', 'slug' => 'club-forum-genie',         'description' => 'Forum annuel entre les ingénieurs de l\'ENSIAS et le monde de l\'entreprise.'],
        ['nom' => 'Club CINDH',                   'slug' => 'club-cindh',               'description' => 'Club d\'initiative et de développement humain de l\'ENSIAS.'],
        ['nom' => 'Club JEE',                     'slug' => 'club-jee',                 'description' => 'Junior Entreprise d\'ENSIAS — accompagne les projets entrepreneuriaux.'],
        ['nom' => 'Club Sportif',                 'slug' => 'club-sportif',             'description' => 'Club multi-sports : football, basketball, tennis de table et plus.'],
        ['nom' => 'Club Fintech',                 'slug' => 'club-fintech',             'description' => 'Club dédié à la finance technologique et aux innovations financières.'],
        ['nom' => 'Club House of Art',            'slug' => 'club-house-of-art',        'description' => 'Club artistique : musique, peinture, photographie et arts numériques.'],
        ['nom' => 'Club ENSIAS Founders',         'slug' => 'club-ensias-founders',     'description' => 'Club des entrepreneurs : startups et projets innovants des étudiants.'],
    ];

    public function run(): void
    {
        $admin = User::whereRaw('LOWER("emailInstitutionnel") = ?', ['admin@ensias.ma'])->first()
                 ?? User::first();

        if (!$admin) {
            $this->command->error('No users found. Run UserSeeder first.');
            return;
        }

        // ── 0. Pre-patch existing groups that have no slug so updateOrCreate
        //       matches them by slug in the next steps ───────────────────────
        Group::whereNull('slug')->each(function (Group $g) {
            $candidate = \Illuminate\Support\Str::slug($g->nom);
            if ($candidate && !Group::where('slug', $candidate)->exists()) {
                $g->update(['slug' => $candidate]);
            }
        });

        $count = 0;

        // ── 1. Filières with year variants ────────────────────────────────
        foreach ($this->filiereAnnee as $fa) {
            $nom  = "{$fa['filiere']} {$fa['annee']}";
            $slug = \Illuminate\Support\Str::slug(strtolower("{$fa['filiere']}-{$fa['annee']}"));

            Group::updateOrCreate(
                ['slug' => $slug],
                [
                    'nom'           => $nom,
                    'categorie'     => 'Filiere',
                    'description'   => "Groupe officiel des étudiants de {$fa['filiere']} – {$fa['annee']} de l'ENSIAS.",
                    'createur_id'   => $admin->id,
                    'filiere_key'   => $fa['filiere'],
                    'annee_filiere' => $fa['annee'],
                    'creeLe'        => now(),
                ]
            );
            $count++;
        }

        // ── 2. Filières without year variant (EITC, INSEC) ───────────────
        foreach ($this->filiereOnly as $fo) {
            $slug = \Illuminate\Support\Str::slug(strtolower($fo['filiere']));
            Group::updateOrCreate(
                ['slug' => $slug],
                [
                    'nom'           => $fo['nom'],
                    'categorie'     => 'Filiere',
                    'description'   => $fo['description'],
                    'createur_id'   => $admin->id,
                    'filiere_key'   => $fo['filiere'],
                    'annee_filiere' => null,
                    'creeLe'        => now(),
                ]
            );
            $count++;
        }

        // ── 3. Clubs ──────────────────────────────────────────────────────
        foreach ($this->clubs as $club) {
            Group::updateOrCreate(
                ['slug' => $club['slug']],
                [
                    'nom'         => $club['nom'],
                    'categorie'   => 'Club',
                    'description' => $club['description'],
                    'createur_id' => $admin->id,
                    'filiere_key' => null,
                    'creeLe'      => now(),
                ]
            );
            $count++;
        }

        // ── 4. Groupe général ─────────────────────────────────────────────
        Group::updateOrCreate(
            ['slug' => 'general'],
            [
                'nom'         => 'Général ENSIAS',
                'categorie'   => 'General',
                'description' => 'Groupe général pour tous les étudiants de l\'ENSIAS.',
                'createur_id' => $admin->id,
                'filiere_key' => null,
                'creeLe'      => now(),
            ]
        );
        $count++;

        $this->command->info("GroupSeeder: {$count} groupes created/updated.");
    }
}

