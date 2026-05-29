<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AutoGroupAssignmentService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function __construct(
        private AutoGroupAssignmentService $service
    ) {}

    /**
     * New user created → assign to filière group if filière is set.
     */
    public function created(User $user): void
    {
        if ($user->filiere) {
            try {
                $this->service->assignUserToFiliereGroup($user);
            } catch (\Throwable $e) {
                Log::error('UserObserver@created: ' . $e->getMessage(), ['user_id' => $user->id]);
            }
        }
    }

    /**
     * User updated → re-assign if filière or année changed.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged(['filiere', 'annee']) && $user->filiere) {
            try {
                $this->service->assignUserToFiliereGroup($user);
            } catch (\Throwable $e) {
                Log::error('UserObserver@updated: ' . $e->getMessage(), ['user_id' => $user->id]);
            }
        }
    }
}
