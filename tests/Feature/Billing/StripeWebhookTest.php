<?php

namespace Tests\Feature\Billing;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function team(string $stripeId): Team
    {
        $user = User::factory()->create();

        return Team::forceCreate([
            'user_id' => $user->id,
            'name' => 'Acme',
            'personal_team' => true,
            'stripe_id' => $stripeId,
        ]);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        config()->set('cashier.webhook.secret', 'whsec_test_secret');

        $response = $this->postJson('stripe/webhook', [
            'id' => 'evt_1',
            'type' => 'customer.subscription.deleted',
        ], ['Stripe-Signature' => 'bogus']);

        $response->assertForbidden();
    }

    public function test_subscription_deleted_event_cancels_local_subscription(): void
    {
        // No webhook secret => Cashier skips signature verification in this test.
        config()->set('cashier.webhook.secret', null);

        $team = $this->team('cus_test123');
        $team->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro',
            'quantity' => 1,
        ]);

        $this->postJson('stripe/webhook', [
            'id' => 'evt_2',
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => 'sub_test123', 'customer' => 'cus_test123']],
        ])->assertOk();

        $this->assertSame('canceled', $team->subscriptions()->first()->stripe_status);
    }
}
