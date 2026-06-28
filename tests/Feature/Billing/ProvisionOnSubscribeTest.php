<?php

namespace Tests\Feature\Billing;

use App\Billing\HostingSubscriptionSync;
use App\Models\Team;
use App\Models\User;
use App\Models\WebHostingAccount;
use App\Services\WebHostingControlPanelManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProvisionOnSubscribeTest extends TestCase
{
    use RefreshDatabase;

    private function pendingAccount(): array
    {
        $user = User::factory()->create();
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => 'Acme',
            'personal_team' => true,
        ]);
        $account = WebHostingAccount::create([
            'team_id' => $team->id,
            'domain' => 'acme.test',
            'username' => 'acme',
            'password' => 'secret',
            'control_panel' => 'cpanel',
            'status' => 'pending',
        ]);

        return [$team, $account];
    }

    public function test_active_subscription_provisions_pending_account(): void
    {
        [$team, $account] = $this->pendingAccount();

        $manager = Mockery::mock(WebHostingControlPanelManager::class);
        $manager->shouldReceive('createAccount')->once()
            ->with(Mockery::on(fn ($data) => $data['domain'] === 'acme.test' && $data['username'] === 'acme'))
            ->andReturnTrue();
        $manager->shouldNotReceive('unsuspendAccount');
        $this->app->bind(WebHostingControlPanelManager::class, fn () => $manager);

        app(HostingSubscriptionSync::class)->apply($team, 'active');

        $this->assertSame('active', $account->fresh()->status);
    }

    public function test_pending_account_is_not_provisioned_while_unpaid(): void
    {
        [$team, $account] = $this->pendingAccount();

        $manager = Mockery::mock(WebHostingControlPanelManager::class);
        $manager->shouldNotReceive('createAccount');
        $manager->shouldReceive('suspendAccount')->andReturnTrue();
        $this->app->bind(WebHostingControlPanelManager::class, fn () => $manager);

        app(HostingSubscriptionSync::class)->apply($team, 'past_due');

        $this->assertSame('suspended', $account->fresh()->status);
    }
}
