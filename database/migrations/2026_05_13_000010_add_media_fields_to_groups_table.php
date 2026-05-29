<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            if (!Schema::hasColumn('groups', 'avatar_url')) {
                $table->string('avatar_url')->nullable()->after('description');
            }
            if (!Schema::hasColumn('groups', 'cover_url')) {
                $table->string('cover_url')->nullable()->after('avatar_url');
            }
            if (!Schema::hasColumn('groups', 'instagram_handle')) {
                $table->string('instagram_handle')->nullable()->after('cover_url');
            }
            if (!Schema::hasColumn('groups', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('instagram_handle');
            }
        });

        // ── Avatar URLs ───────────────────────────────────────────────────────
        $clubs = [
            'club-bridge'          => '/groups/clubs/club-bridge.jpg',
            'club-cindh'           => '/groups/clubs/club-cindh.jpg',
            'club-eitc'            => '/groups/clubs/club-eitc.jpg',
            'club-ensias-founders' => '/groups/clubs/club-ensias-founders.jpg',
            'club-fintech'         => '/groups/clubs/club-fintech.jpg',
            'club-forum-genie'     => '/groups/clubs/club-forum-genie.jpg',
            'club-house-of-art'    => '/groups/clubs/club-house-of-art.jpg',
            'club-insec'           => '/groups/clubs/club-insec.jpg',
            'club-japonnais'       => '/groups/clubs/club-japonnais.jpg',
            'club-jee'             => '/groups/clubs/club-jee.jpg',
            'club-neurodynamics'   => '/groups/clubs/club-neurodynamics.jpg',
            'club-quraan'          => '/groups/clubs/club-quraan.jpg',
            'club-sportif'         => '/groups/clubs/club-sportif.jpg',
        ];

        foreach ($clubs as $slug => $avatarUrl) {
            DB::table('groups')
                ->where('slug', $slug)
                ->update(['avatar_url' => $avatarUrl]);
        }

        // ── Instagram handles ─────────────────────────────────────────────────
        $instagrams = [
            'club-bridge'          => ['handle' => 'ensias_bridge',       'url' => 'https://instagram.com/ensias_bridge'],
            'club-jee'             => ['handle' => 'clubjee_ensias',      'url' => 'https://instagram.com/clubjee_ensias'],
            'club-neurodynamics'   => ['handle' => 'neurodynamics_ensias','url' => 'https://instagram.com/neurodynamics_ensias'],
            'club-sportif'         => ['handle' => 'clubsportif_ensias',  'url' => 'https://instagram.com/clubsportif_ensias'],
            'club-fintech'         => ['handle' => 'fintech_ensias',      'url' => 'https://instagram.com/fintech_ensias'],
            'club-house-of-art'    => ['handle' => 'hoa_ensias',          'url' => 'https://instagram.com/hoa_ensias'],
            'club-ensias-founders' => ['handle' => 'ensiasfounders',      'url' => 'https://instagram.com/ensiasfounders'],
            'club-forum-genie'     => ['handle' => 'forum_genie_ensias',  'url' => 'https://instagram.com/forum_genie_ensias'],
        ];

        foreach ($instagrams as $slug => $ig) {
            DB::table('groups')
                ->where('slug', $slug)
                ->update([
                    'instagram_handle' => $ig['handle'],
                    'instagram_url'    => $ig['url'],
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'cover_url', 'instagram_handle', 'instagram_url']);
        });
    }
};
