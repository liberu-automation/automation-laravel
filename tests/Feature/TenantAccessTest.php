<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_team_they_own(): void
    {
        $user = User::factory()->create();
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => 'Owned Team',
            'personal_team' => true,
        ]);

        $this->assertTrue($user->canAccessTenant($team));
    }

    public function test_user_can_access_team_they_are_a_member_of(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::forceCreate([
            'user_id' => $owner->id,
            'name' => 'Shared Team',
            'personal_team' => false,
        ]);
        $member->teams()->attach($team, ['role' => 'admin']);

        $this->assertTrue($member->fresh()->canAccessTenant($team));
    }

    public function test_user_cannot_access_team_they_do_not_belong_to(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreignTeam = Team::forceCreate([
            'user_id' => $other->id,
            'name' => 'Foreign Team',
            'personal_team' => true,
        ]);

        $this->assertFalse($user->canAccessTenant($foreignTeam));
    }
}
