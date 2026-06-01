<?php

namespace Tests\Feature;

use Tests\TestCase;

class SocialstreamConfigTest extends TestCase
{
    public function test_socialstream_config_has_social_media_providers(): void
    {
        $providers = config('socialstream.providers');

        $this->assertNotEmpty($providers);

        $expectedProviders = [
            'bitbucket',
            'facebook',
            'github',
            'gitlab',
            'google',
            'linkedin',
            'linkedinOpenId',
            'slack',
            'twitter-oauth-2',
        ];

        foreach ($expectedProviders as $provider) {
            $this->assertArrayHasKey(
                $provider,
                $providers,
                "Provider {$provider} is missing from socialstream config"
            );
            $this->assertTrue(
                $providers[$provider]['enabled'],
                "Provider {$provider} should be enabled"
            );
        }

        $this->assertArrayNotHasKey(
            'twitter-oauth-1',
            $providers,
            'Twitter OAuth 1.0 must not be configured (requires live API keys)'
        );
    }

    public function test_socialstream_show_is_enabled(): void
    {
        $this->assertTrue(config('socialstream.show'));
    }
}
