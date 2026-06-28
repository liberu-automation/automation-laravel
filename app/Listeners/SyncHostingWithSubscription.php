<?php

namespace App\Listeners;

use App\Billing\HostingSubscriptionSync;
use App\Models\Team;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * On a Stripe subscription webhook, reconcile the Team's hosting with its status.
 */
class SyncHostingWithSubscription
{
    public function __construct(private HostingSubscriptionSync $sync) {}

    public function handle(WebhookHandled $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? '';

        if (! str_starts_with($type, 'customer.subscription.')) {
            return;
        }

        $object = $payload['data']['object'] ?? [];

        $team = Cashier::findBillable($object['customer'] ?? null);

        if (! $team instanceof Team) {
            return;
        }

        $status = $type === 'customer.subscription.deleted'
            ? 'canceled'
            : ($object['status'] ?? null);

        $this->sync->apply($team, $status);
    }
}
