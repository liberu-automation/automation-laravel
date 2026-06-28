<?php

namespace Tests\Feature\Billing;

use App\Filament\App\Pages\Billing;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class BillingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('billing.plans', [
            'starter' => ['name' => 'Starter', 'price_id' => 'price_starter', 'features' => []],
            'no_price' => ['name' => 'No Price', 'price_id' => null, 'features' => []],
        ]);
    }

    public function test_page_lists_configured_plans(): void
    {
        $this->assertSame(['starter', 'no_price'], array_map(
            fn ($p) => $p->key,
            (new Billing)->plans()
        ));
    }

    public function test_subscribe_rejects_unknown_plan(): void
    {
        $this->expectException(NotFoundHttpException::class);

        (new Billing)->subscribe('does_not_exist');
    }

    public function test_subscribe_rejects_plan_without_price(): void
    {
        $this->expectException(NotFoundHttpException::class);

        (new Billing)->subscribe('no_price');
    }
}
