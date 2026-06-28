<?php

namespace Tests\Feature\Billing;

use App\Models\Team;
use App\Models\User;
use App\Models\WebHostingAccount;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    private function team(string $name): Team
    {
        $user = User::factory()->create();

        return Team::forceCreate([
            'user_id' => $user->id,
            'name' => $name,
            'personal_team' => true,
        ]);
    }

    private function actAsTenant(Team $team): void
    {
        $this->actingAs($team->owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($team);
    }

    private function account(Team $team, string $domain): WebHostingAccount
    {
        return WebHostingAccount::withoutGlobalScopes()->create([
            'team_id' => $team->id,
            'domain' => $domain,
            'username' => $domain,
            'password' => 'secret',
            'control_panel' => 'cpanel',
            'status' => 'active',
        ]);
    }

    public function test_queries_are_scoped_to_the_current_filament_tenant(): void
    {
        $a = $this->team('A');
        $b = $this->team('B');
        $this->account($a, 'a.test');
        $this->account($b, 'b.test');

        $this->actAsTenant($a);

        $domains = WebHostingAccount::query()->pluck('domain');

        $this->assertSame(['a.test'], $domains->all());
    }

    public function test_no_scope_without_a_tenant(): void
    {
        $a = $this->team('A');
        $b = $this->team('B');
        $this->account($a, 'a.test');
        $this->account($b, 'b.test');

        $this->assertCount(2, WebHostingAccount::all());
    }

    public function test_team_id_is_set_from_tenant_on_create(): void
    {
        $a = $this->team('A');

        $this->actAsTenant($a);

        $account = WebHostingAccount::create([
            'domain' => 'new.test',
            'username' => 'new',
            'password' => 'secret',
            'control_panel' => 'cpanel',
            'status' => 'active',
        ]);

        $this->assertSame($a->id, $account->team_id);
    }
}
