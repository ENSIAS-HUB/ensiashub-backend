<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Fix EITC club (slug='', categorie='Club') ─────────────────────
        DB::table('groups')
            ->where('nom', 'EITC')
            ->where('categorie', 'Club')
            ->where('slug', '')
            ->update([
                'slug'       => 'eitc',
                'avatar_url' => '/groups/clubs/club-eitc.jpg',
            ]);

        // ── 2. Fix INSEC club (slug='', categorie='Club') ────────────────────
        DB::table('groups')
            ->where('nom', 'INSEC')
            ->where('categorie', 'Club')
            ->where('slug', '')
            ->update([
                'slug'       => 'insec',
                'avatar_url' => '/groups/clubs/club-insec.jpg',
            ]);

        // ── 3. Fix Filière rows that were wrongly given a club avatar_url ────
        //    (slug=eitc/insec but categorie=Filiere — these are filière groups)
        DB::table('groups')
            ->whereIn('slug', ['eitc', 'insec'])
            ->where('categorie', 'Filiere')
            ->update(['avatar_url' => null]);

        // ── 4. Delete empty-slug duplicate Club entries ──────────────────────
        //    These are ghost duplicates of clubs that already have proper slugs.
        //    First remove FK-dependent rows, then delete the groups.
        $officialSlugs = [
            'club-bridge', 'club-cindh', 'club-eitc', 'eitc',
            'club-ensias-founders', 'club-fintech', 'club-forum-genie',
            'club-house-of-art', 'club-insec', 'insec', 'club-japonnais',
            'club-jee', 'club-neurodynamics', 'club-quraan', 'club-sportif',
        ];

        // Names whose empty-slug duplicates should be deleted
        $duplicateNames = [
            'Club Quraan', 'Club Bridge', 'Club Neurodynamics',
            'Club CINDH', 'Club JEE', 'Club Sportif', 'Club Fintech',
            'Club House of Art', 'Club ENSIAS Founders',
        ];

        $duplicateIds = DB::table('groups')
            ->where('categorie', 'Club')
            ->where('slug', '')
            ->whereIn('nom', $duplicateNames)
            ->pluck('id')
            ->toArray();

        if (!empty($duplicateIds)) {
            // Remove adhesion_groups memberships first (FK)
            DB::table('adhesion_groups')
                ->whereIn('group_id', $duplicateIds)
                ->delete();

            // Remove publications linked to these ghost groups
            $pubIds = DB::table('publications')
                ->whereIn('groupe_id', $duplicateIds)
                ->pluck('id')
                ->toArray();
            if (!empty($pubIds)) {
                DB::table('post_media')
                    ->whereIn('publication_id', $pubIds)
                    ->delete();
                DB::table('publications')
                    ->whereIn('id', $pubIds)
                    ->delete();
            }

            // Delete the ghost groups
            DB::table('groups')
                ->whereIn('id', $duplicateIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // Restore eitc/insec Filiere avatar_url (from migration 000010)
        DB::table('groups')
            ->whereIn('slug', ['eitc', 'insec'])
            ->where('categorie', 'Filiere')
            ->update(['avatar_url' => null]);

        // Clear EITC/INSEC club fixes
        DB::table('groups')
            ->whereIn('slug', ['eitc', 'insec'])
            ->where('categorie', 'Club')
            ->update(['slug' => '', 'avatar_url' => null]);
    }
};
