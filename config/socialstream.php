<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Socialstream Configuration
    |--------------------------------------------------------------------------
    |
    | Enable the social providers you wish to support. Twitter OAuth 1.0 is
    | intentionally excluded because it requires live API keys to test and use.
    |
    */

    'show' => true,

    'providers' => [
        'bitbucket' => ['enabled' => true],
        'facebook' => ['enabled' => true],
        'github' => ['enabled' => true],
        'gitlab' => ['enabled' => true],
        'google' => ['enabled' => true],
        'linkedin' => ['enabled' => true],
        'linkedinOpenId' => ['enabled' => true],
        'slack' => ['enabled' => true],
        'twitter-oauth-2' => ['enabled' => true],
        // twitter-oauth-1 intentionally excluded
    ],

    'generates_providers_redirects' => true,
    'generates_missing_emails' => true,
];
