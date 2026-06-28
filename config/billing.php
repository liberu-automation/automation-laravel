<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Hosting subscription tiers offered to Teams. `price_id` is the Stripe
    | Price ID (create the product/price in the Stripe dashboard, then set the
    | matching env var). A plan with a null price_id is hidden from checkout.
    |
    */

    'plans' => [

        'starter' => [
            'name' => 'Starter',
            'price_id' => env('STRIPE_PRICE_STARTER'),
            'features' => [
                '1 hosting account',
                'Community support',
            ],
        ],

        'pro' => [
            'name' => 'Pro',
            'price_id' => env('STRIPE_PRICE_PRO'),
            'features' => [
                'Unlimited hosting accounts',
                'Priority support',
            ],
        ],

    ],

];
