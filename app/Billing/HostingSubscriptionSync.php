<?php

namespace App\Billing;

use App\Models\Team;
use App\Models\WebHostingAccount;
use App\Services\WebHostingControlPanelManager;

/**
 * Reconciles a Team's hosting accounts with its Stripe subscription state.
 */
class HostingSubscriptionSync
{
    /**
     * Stripe subscription statuses that entitle a Team to live hosting.
     */
    private const ACTIVE_STATUSES = ['active', 'trialing'];

    public function apply(Team $team, ?string $stripeStatus): void
    {
        $shouldBeActive = in_array($stripeStatus, self::ACTIVE_STATUSES, true);

        foreach ($team->webHostingAccounts as $account) {
            $shouldBeActive
                ? $this->activate($account)
                : $this->suspend($account);
        }
    }

    private function activate(WebHostingAccount $account): void
    {
        if ($account->status === 'active') {
            return;
        }

        $this->manager($account->control_panel)->unsuspendAccount($account->username);
        $account->update(['status' => 'active']);
    }

    private function suspend(WebHostingAccount $account): void
    {
        if ($account->status === 'suspended') {
            return;
        }

        $this->manager($account->control_panel)->suspendAccount($account->username);
        $account->update(['status' => 'suspended']);
    }

    private function manager(string $controlPanel): WebHostingControlPanelManager
    {
        return app()->makeWith(WebHostingControlPanelManager::class, [
            'controlPanel' => $controlPanel,
        ]);
    }
}
