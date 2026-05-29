<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\PostMedia;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstagramPostsSeeder extends Seeder
{
    public function run(): void
    {
        // Find any superAdmin to attribute posts to
        $superAdmin = User::whereJsonContains('roles', 'superAdmin')->first()
            ?? User::first();

        if (!$superAdmin) {
            $this->command->error('Aucun utilisateur trouvé — impossible de seeder les posts Instagram.');
            return;
        }

        $imports = [
            // ── Club Bridge ──────────────────────────────────────────────────
            [
                'group_slug'    => 'club-bridge',
                'content'       => "We connect the Alumni of ENSIAS with current students, opening the door for collaborations & internships! 🎓🤝\n#ENSIAS #Bridge #Alumni",
                'instagram_url' => 'https://instagram.com/p/club-bridge-001',
                'imported_at'   => '2024-11-15 10:00:00',
                'media'         => [
                    '/posts/instagram/club-bridge/post-001.jpg',
                    '/posts/instagram/club-bridge/post-002.jpg',
                ],
            ],
            [
                'group_slug'    => 'club-bridge',
                'content'       => "BRIEFINGS — Nos sessions de mentoring avec les alumni ENSIAS 📚\n#Bridge #Mentoring",
                'instagram_url' => 'https://instagram.com/p/club-bridge-002',
                'imported_at'   => '2024-10-20 14:00:00',
                'media'         => [
                    '/posts/instagram/club-bridge/post-003.jpg',
                ],
            ],

            // ── Club JEE ─────────────────────────────────────────────────────
            [
                'group_slug'    => 'club-jee',
                'content'       => "Session de formation Java Enterprise Edition — inscrivez-vous ! 💻\n#JEE #ENSIAS #Dev",
                'instagram_url' => 'https://instagram.com/p/club-jee-001',
                'imported_at'   => '2024-11-01 09:00:00',
                'media'         => [
                    '/posts/instagram/club-jee/post-001.jpg',
                ],
            ],

            // ── Club Neurodynamics ────────────────────────────────────────────
            [
                'group_slug'    => 'club-neurodynamics',
                'content'       => "Introduction au Machine Learning — atelier pratique avec TensorFlow 🤖\n#IA #ML #ENSIAS",
                'instagram_url' => 'https://instagram.com/p/club-neurodynamics-001',
                'imported_at'   => '2024-10-28 11:00:00',
                'media'         => [
                    '/posts/instagram/club-neurodynamics/post-001.jpg',
                ],
            ],

            // ── Club Sportif ──────────────────────────────────────────────────
            [
                'group_slug'    => 'club-sportif',
                'content'       => "Tournoi inter-filières — inscriptions ouvertes ! 🏆⚽\n#Sport #ENSIAS #Tournoi",
                'instagram_url' => 'https://instagram.com/p/club-sportif-001',
                'imported_at'   => '2024-11-05 15:00:00',
                'media'         => [
                    '/posts/instagram/club-sportif/post-001.jpg',
                ],
            ],

            // ── Club Fintech ──────────────────────────────────────────────────
            [
                'group_slug'    => 'club-fintech',
                'content'       => "Conférence Finance & Technologie — les nouvelles frontières du paiement digital 💳\n#Fintech #ENSIAS",
                'instagram_url' => 'https://instagram.com/p/club-fintech-001',
                'imported_at'   => '2024-10-10 10:30:00',
                'media'         => [
                    '/posts/instagram/club-fintech/post-001.jpg',
                ],
            ],
        ];

        foreach ($imports as $data) {
            $group = Group::where('slug', $data['group_slug'])->first();

            if (!$group) {
                $this->command->warn("⚠️  Groupe introuvable : {$data['group_slug']}");
                continue;
            }

            $pub = Publication::updateOrCreate(
                [
                    'instagram_url' => $data['instagram_url'],
                    'groupe_id'     => $group->id,
                ],
                [
                    'user_id'           => $superAdmin->id,
                    'contenu'           => $data['content'],
                    'visibility'        => 'group',
                    'statutValidation'  => 'approved',
                    'source'            => 'instagram_import',
                    'imported_at'       => $data['imported_at'],
                    'publishedAt'       => $data['imported_at'],
                ]
            );

            foreach ($data['media'] as $index => $url) {
                PostMedia::updateOrCreate(
                    ['publication_id' => $pub->id, 'url' => $url],
                    ['type' => 'image', 'order' => $index]
                );
            }

            $this->command->info("✅ Post importé pour {$data['group_slug']}");
        }
    }
}
