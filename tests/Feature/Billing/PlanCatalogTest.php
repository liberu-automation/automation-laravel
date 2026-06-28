<?php

namespace Tests\Feature\Billing;

use App\Billing\Plan;
use Tests\TestCase;

class PlanCatalogTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('billing.plans', [
            'starter' => ['name' => 'Starter', 'price_id' => 'price_starter', 'features' => ['1 site']],
            'pro' => ['name' => 'Pro', 'price_id' => 'price_pro', 'features' => ['Unlimited']],
        ]);
    }

    public function test_all_returns_configured_plans(): void
    {
        $plans = Plan::all();

        $this->assertCount(2, $plans);
        $this->assertContainsOnlyInstancesOf(Plan::class, $plans);
        $this->assertSame(['starter', 'pro'], array_map(fn (Plan $p) => $p->key, $plans));
    }

    public function test_find_returns_matching_plan(): void
    {
        $plan = Plan::find('pro');

        $this->assertInstanceOf(Plan::class, $plan);
        $this->assertSame('Pro', $plan->name);
        $this->assertSame('price_pro', $plan->priceId);
        $this->assertSame(['Unlimited'], $plan->features);
    }

    public function test_find_returns_null_for_unknown_plan(): void
    {
        $this->assertNull(Plan::find('enterprise'));
    }
}
