<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SocialstreamConfigTest extends TestCase
{
    public function test_socialstream_config_has_social_media_providers(): void
    {
        $providers = config('socialstream.providers');

        $this->assertIsArray($providers);

        $expected = [
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

        foreach ($expected as $provider) {
            $this->assertArrayHasKey($provider, $providers, "Provider {$provider} is not enabled in socialstream config");
            $this->assertTrue(
                is_array($providers[$provider]) && ($providers[$provider]['enabled'] ?? false) === true,
                "Provider {$provider} is not enabled in socialstream config"
            );
        }

        // Ensure twitter oauth 1 is not present
        $this->assertArrayNotHasKey('twitter-oauth-1', $providers);
    }
}
