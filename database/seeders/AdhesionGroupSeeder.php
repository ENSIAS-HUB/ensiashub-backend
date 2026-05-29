<?php

namespace Database\Seeders;

use App\Models\AdhesionGroup;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdhesionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Group::all();
        $users  = User::all();

        if ($groups->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Pas de groupes ou utilisateurs — AdhesionGroupSeeder ignoré.');
            return;
        }

        // Associe chaque user aux 3 premiers groupes
        foreach ($users->take(5) as $user) {
            foreach ($groups->take(3) as $group) {
                if (!Group::find($group->id)) {
                    continue;
                }
                AdhesionGroup::firstOrCreate(
                    ['user_id' => $user->id, 'group_id' => $group->id],
                    [
                        'statut'     => 'Approuve',
                        'role'       => 'Membre',
                        'joinedAt'   => now(),
                        'reviewedAt' => now(),
                    ]
                );
            }
        }
    }
}