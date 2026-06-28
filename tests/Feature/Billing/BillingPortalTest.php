<?php

namespace Tests\Feature\Billing;

use App\Filament\App\Pages\Billing;
use App\Models\Team;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class BillingPortalTest extends TestCase
{
    use RefreshDatabase;

    private function actAsTeam(?string $stripeId = null): Team
    {
        $user = User::factory()->create();
        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => 'Acme',
            'personal_team' => true,
            'stripe_id' => $stripeId,
        ]);

        $this->actingAs($team->owner);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        Filament::setTenant($team);

        return $team;
    }

    public function test_no_invoices_when_team_has_no_stripe_customer(): void
    {
        $this->actAsTeam(stripeId: null);

        $this->assertSame([], (new Billing)->invoices());
    }

    public function test_has_active_subscription_false_without_subscription(): void
    {
        $this->actAsTeam(stripeId: 'cus_x');

        $this->assertFalse((new Billing)->hasActiveSubscription());
    }

    public function test_manage_billing_aborts_without_stripe_customer(): void
    {
        $this->actAsTeam(stripeId: null);

        $this->expectException(NotFoundHttpException::class);

        (new Billing)->manageBilling();
    }
}
