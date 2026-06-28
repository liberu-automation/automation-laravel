<?php

namespace Tests\Feature\Billing;

use App\Billing\HostingSubscriptionSync;
use App\Models\Team;
use App\Models\User;
use App\Models\WebHostingAccount;
use App\Services\WebHostingControlPanelManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Events\WebhookHandled;
use Mockery;
use Tests\TestCase;

class HostingSubscriptionSyncTest extends TestCase
{
    use RefreshDatabase;

    private function teamWithAccount(string $status = 'active'): array
    {
        $user = User::factory()->create();
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => 'Acme',
            'personal_team' => true,
            'stripe_id' => 'cus_x',
        ]);
        $account = WebHostingAccount::create([
            'team_id' => $team->id,
            'domain' => 'acme.test',
            'username' => 'acme',
            'password' => 'secret',
            'control_panel' => 'cpanel',
            'status' => $status,
        ]);

        return [$team, $account];
    }

    public function test_active_subscription_unsuspends_team_accounts(): void
    {
        [$team, $account] = $this->teamWithAccount('suspended');

        $manager = Mockery::mock(WebHostingControlPanelManager::class);
        $manager->shouldReceive('unsuspendAccount')->once()->with('acme')->andReturnTrue();
        $manager->shouldNotReceive('suspendAccount');
        $this->app->bind(WebHostingControlPanelManager::class, fn () => $manager);

        app(HostingSubscriptionSync::class)->apply($team, 'active');

        $this->assertSame('active', $account->fresh()->status);
    }

    public function test_canceled_subscription_suspends_team_accounts(): void
    {
        [$team, $account] = $this->teamWithAccount('active');

        $manager = Mockery::mock(WebHostingControlPanelManager::class);
        $manager->shouldReceive('suspendAccount')->once()->with('acme')->andReturnTrue();
        $manager->shouldNotReceive('unsuspendAccount');
        $this->app->bind(WebHostingControlPanelManager::class, fn () => $manager);

        app(HostingSubscriptionSync::class)->apply($team, 'canceled');

        $this->assertSame('suspended', $account->fresh()->status);
    }

    public function test_webhook_handled_event_drives_the_sync(): void
    {
        [$team, $account] = $this->teamWithAccount('active');

        $manager = Mockery::mock(WebHostingControlPanelManager::class);
        $manager->shouldReceive('suspendAccount')->once()->with('acme')->andReturnTrue();
        $this->app->bind(WebHostingControlPanelManager::class, fn () => $manager);

        event(new WebhookHandled([
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['customer' => 'cus_x', 'status' => 'canceled']],
        ]));

        $this->assertSame('suspended', $account->fresh()->status);
    }
}
