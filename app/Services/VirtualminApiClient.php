<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class VirtualminApiClient
{
    private $client;
    private $baseUrl;
    private $username;
    private $password;

    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->client = new Client();
    }

    public function createAccount(array $data): bool
    {
        $response = $this->makeRequest('create-domain', [
            'domain' => $data['domain'],
            'user' => $data['username'],
            'pass' => $data['password'],
            // Add other necessary parameters
        ]);

        return $response['status'] === 'success';
    }

    public function suspendAccount(string $domain): bool
    {
        $response = $this->makeRequest('disable-domain', [
            'domain' => $domain,
        ]);

        return $response['status'] === 'success';
    }

    public function unsuspendAccount(string $domain): bool
    {
        $response = $this->makeRequest('enable-domain', [
            'domain' => $domain,
        ]);

        return $response['status'] === 'success';
    }

    public function deleteAccount(string $domain): bool
    {
        $response = $this->makeRequest('delete-domain', [
            'domain' => $domain,
        ]);

        return $response['status'] === 'success';
    }

    private function makeRequest(string $program, array $params): array
    {
        $url = "{$this->baseUrl}/virtual-server/remote.cgi";
        $params = array_merge([
            'program' => $program,
            'json' => 1,
        ], $params);

        try {
            $response = $this->client->request('GET', $url, [
                'query' => $params,
                'auth' => [$this->username, $this->password],
            ]);

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw new Exception("Virtualmin API request failed: " . $e->getMessage());
        }
    }
}