<?php

namespace App\Services;

use App\Models\User;

class TeamManagementService
{
    /**
     * Assign a newly registered user to their personal team.
     */
    public function assignUserToDefaultTeam(User $user): void
    {
        if ($user->ownedTeams()->count() === 0) {
            $team = $user->ownedTeams()->create([
                'name'          => $user->name . "'s Team",
                'personal_team' => true,
            ]);

            $user->switchTeam($team);
        }
    }
}
