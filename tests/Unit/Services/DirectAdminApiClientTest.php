<?php

namespace Tests\Unit\Services;

use App\Services\DirectAdminApiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class DirectAdminApiClientTest extends TestCase
{
    private $client;
    private $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);

        $this->client = new DirectAdminApiClient('http://example.com', 'username', 'password');
        $this->client->setHttpClient($httpClient);
    }

    public function testCreateAccount()
    {
        $this->mockHandler->append(new Response(200, [], 'error=0&result=success'));

        $result = $this->client->createAccount([
            'domain' => 'example.com',
            'username' => 'testuser',
            'password' => 'testpass',
        ]);

        $this->assertTrue($result);
    }

    public function testSuspendAccount()
    {
        $this->mockHandler->append(new Response(200, [], 'error=0&result=success'));

        $result = $this->client->suspendAccount('testuser');

        $this->assertTrue($result);
    }

    public function testUnsuspendAccount()
    {
        $this->mockHandler->append(new Response(200, [], 'error=0&result=success'));

        $result = $this->client->unsuspendAccount('testuser');

        $this->assertTrue($result);
    }

    public function testDeleteAccount()
    {
        $this->mockHandler->append(new Response(200, [], 'error=0&result=success'));

        $result = $this->client->deleteAccount('testuser');

        $this->assertTrue($result);
    }
}