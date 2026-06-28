<?php

namespace App\Filament\App\Pages;

use App\Billing\Plan;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Invoice;

class Billing extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected string $view = 'filament.app.pages.billing';

    public static function getNavigationLabel(): string
    {
        return 'Billing';
    }

    /**
     * @return array<int, Plan>
     */
    public function plans(): array
    {
        return Plan::all();
    }

    public function currentTeam(): Team
    {
        return Filament::getTenant();
    }

    /**
     * Start a Stripe Checkout session for the given plan on the current Team.
     */
    public function subscribe(string $planKey): Checkout
    {
        $plan = Plan::find($planKey);

        abort_unless($plan !== null && $plan->priceId !== null, 404);

        return $this->currentTeam()
            ->newSubscription('default', $plan->priceId)
            ->checkout([
                'success_url' => static::getUrl().'?checkout=success',
                'cancel_url' => static::getUrl().'?checkout=cancelled',
            ]);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->currentTeam()->subscribed('default');
    }

    /**
     * Past invoices for the current Team. Empty until it has a Stripe customer.
     *
     * @return array<int, Invoice>
     */
    public function invoices(): array
    {
        $team = $this->currentTeam();

        return $team->hasStripeId() ? $team->invoices()->all() : [];
    }

    /**
     * Redirect to the Stripe-hosted billing portal (manage card, cancel, invoices).
     */
    public function manageBilling(): RedirectResponse
    {
        $team = $this->currentTeam();

        abort_unless($team->hasStripeId(), 404);

        return $team->redirectToBillingPortal(static::getUrl());
    }
}
