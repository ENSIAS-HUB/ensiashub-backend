<?php

namespace App\Console\Commands;

use App\Models\AdhesionGroup;
use App\Models\Group;
use App\Models\User;
use App\Services\AutoGroupAssignmentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixGroupAssignments extends Command
{
    protected $signature   = 'groups:fix-assignments {--dry-run : Preview changes without writing to the database}';
    protected $description = 'Remove stale filière memberships and re-assign every user to their correct filière group';

    public function handle(AutoGroupAssignmentService $service): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY-RUN mode — no changes will be written.');
        }

        // Collect all filière group IDs
        $filiereGroupIds = Group::where('categorie', 'Filiere')->pluck('id');

        if ($filiereGroupIds->isEmpty()) {
            $this->error('No filière groups found in the database.');
            return self::FAILURE;
        }

        $this->info("Found {$filiereGroupIds->count()} filière group(s).");
        $this->newLine();

        // ── Step 1: Find users who have MORE THAN ONE active filière membership ──
        $usersWithMultiple = AdhesionGroup::whereIn('group_id', $filiereGroupIds)
            ->where('statut', 'Approuve')
            ->select('user_id', DB::raw('count(*) as cnt'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        $this->info("Users with multiple filière memberships: {$usersWithMultiple->count()}");

        // ── Step 2: For each affected user, delete ALL filière memberships ──
        $purged = 0;
        foreach ($usersWithMultiple as $userId) {
            $rows = AdhesionGroup::where('user_id', $userId)
                ->whereIn('group_id', $filiereGroupIds)
                ->get();

            if ($this->getOutput()->isVerbose()) {
                $groupNames = $rows->map(fn ($r) => $r->group_id)->implode(', ');
                $this->line("  Purging {$rows->count()} filière memberships for user {$userId}: [{$groupNames}]");
            }

            if (!$dryRun) {
                AdhesionGroup::where('user_id', $userId)
                    ->whereIn('group_id', $filiereGroupIds)
                    ->delete();
            }

            $purged += $rows->count();
        }

        $this->info("Purged {$purged} stale filière membership row(s)." . ($dryRun ? ' (dry-run)' : ''));
        $this->newLine();

        // ── Step 3: Re-assign every user with a filière ──
        $users     = User::whereNotNull('filiere')->get();
        $assigned  = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ($users as $user) {
            if ($dryRun) {
                $this->line("  [dry-run] Would assign {$user->prenom} {$user->nom} → {$user->filiere} {$user->annee}");
                $assigned++;
                continue;
            }

            try {
                $service->assignUserToFiliereGroup($user);
                $assigned++;
                $this->line("  ✓ {$user->prenom} {$user->nom} → {$user->filiere} {$user->annee}");
            } catch (\Throwable $e) {
                $errors++;
                $this->warn("  ✗ {$user->prenom} {$user->nom}: " . $e->getMessage());
            }
        }

        // Users with filière=null cannot be assigned
        $skipped = User::whereNull('filiere')->count();

        $this->newLine();
        $this->info("Assignment complete: {$assigned} assigned, {$skipped} skipped (no filière), {$errors} errors.");

        return self::SUCCESS;
    }
}
