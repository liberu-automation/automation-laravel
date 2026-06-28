<?php

namespace App\Filament\App\Pages;

use App\Billing\Plan;
use App\Models\Team;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Laravel\Cashier\Checkout;

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
}
