<?php

namespace App\Billing;

/**
 * A subscription plan, read from config/billing.php.
 */
final class Plan
{
    /**
     * @param  array<int, string>  $features
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly ?string $priceId,
        public readonly array $features = [],
    ) {}

    /**
     * All configured plans, in config order.
     *
     * @return array<int, self>
     */
    public static function all(): array
    {
        return collect(config('billing.plans', []))
            ->map(fn (array $plan, string $key) => self::fromConfig($key, $plan))
            ->values()
            ->all();
    }

    public static function find(string $key): ?self
    {
        $plan = config("billing.plans.{$key}");

        return is_array($plan) ? self::fromConfig($key, $plan) : null;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private static function fromConfig(string $key, array $plan): self
    {
        return new self(
            key: $key,
            name: $plan['name'],
            priceId: $plan['price_id'] ?? null,
            features: $plan['features'] ?? [],
        );
    }
}
