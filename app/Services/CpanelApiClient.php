<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class CpanelApiClient
{
    private $client;
    private $baseUrl;
    private $username;
    private $apiToken;

    public function __construct(string $baseUrl, string $username, string $apiToken)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->apiToken = $apiToken;
        $this->client = new Client();
    }

    public function createAccount(array $data): bool
    {
        $response = $this->makeRequest('createacct', [
            'username' => $data['username'],
            'domain' => $data['domain'],
            'password' => $data['password'],
            'plan' => $data['package'] ?? '',
        ]);

        return $response['result'][0]['status'] === 1;
    }

    public function suspendAccount(string $username): bool
    {
        $response = $this->makeRequest('suspendacct', [
            'user' => $username,
        ]);

        return $response['result'][0]['status'] === 1;
    }

    public function unsuspendAccount(string $username): bool
    {
        $response = $this->makeRequest('unsuspendacct', [
            'user' => $username,
        ]);

        return $response['result'][0]['status'] === 1;
    }

    public function deleteAccount(string $username): bool
    {
        $response = $this->makeRequest('removeacct', [
            'username' => $username,
        ]);

        return $response['result'][0]['status'] === 1;
    }

    private function makeRequest(string $function, array $params): array
    {
        $url = "{$this->baseUrl}/json-api/{$function}";
        
        try {
            $response = $this->client->request('GET', $url, [
                'query' => $params,
                'headers' => [
                    'Authorization' => "WHM {$this->username}:{$this->apiToken}",
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (Exception $e) {
            throw new Exception("cPanel API request failed: " . $e->getMessage());
        }
    }
}