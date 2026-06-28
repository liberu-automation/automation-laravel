<?php

namespace Tests\Feature\Billing;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Cashier;
use Tests\TestCase;

class CashierSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_is_the_billable_cashier_model(): void
    {
        $this->assertContains(Billable::class, class_uses_recursive(Team::class));
        $this->assertSame(Team::class, Cashier::$customerModel);
    }

    public function test_teams_table_has_stripe_columns(): void
    {
        foreach (['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'] as $column) {
            $this->assertTrue(
                Schema::hasColumn('teams', $column),
                "teams table missing Cashier column [{$column}]"
            );
        }
    }

    public function test_team_exposes_cashier_subscription_api(): void
    {
        $team = new Team;

        $this->assertFalse($team->hasStripeId());
        $this->assertTrue(method_exists($team, 'subscriptions'));
    }
}
