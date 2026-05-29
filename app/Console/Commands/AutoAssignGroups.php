<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AutoGroupAssignmentService;
use Illuminate\Console\Command;

class AutoAssignGroups extends Command
{
    protected $signature   = 'groups:auto-assign-all';
    protected $description = 'Auto-assign every user with a filière to their filière group (idempotent)';

    public function handle(AutoGroupAssignmentService $service): int
    {
        $users = User::whereNotNull('filiere')->get();

        if ($users->isEmpty()) {
            $this->info('No users with a filière found.');
            return self::SUCCESS;
        }

        $assigned = 0;
        $skipped  = 0;

        foreach ($users as $user) {
            try {
                $service->assignUserToFiliereGroup($user);
                $assigned++;
                $this->line("  ✓ {$user->prenom} {$user->nom} → {$user->filiere} {$user->annee}");
            } catch (\Throwable $e) {
                $skipped++;
                $this->warn("  ✗ {$user->prenom} {$user->nom}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done. {$assigned} assigned/verified, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
