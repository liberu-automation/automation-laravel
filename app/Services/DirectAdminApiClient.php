<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;

class DirectAdminApiClient
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
        $response = $this->makeRequest('ACCOUNT_CREATOR', [
            'action' => 'create',
            'domain' => $data['domain'],
            'username' => $data['username'],
            'passwd' => $data['password'],
            'passwd2' => $data['password'],
            'email' => $data['email'] ?? '',
            'package' => $data['package'] ?? '',
            'ip' => $data['ip'] ?? '',
            'notify' => 'no',
        ]);

        return $response['error'] === '0';
    }

    public function suspendAccount(string $username): bool
    {
        $response = $this->makeRequest('SUSPEND_USER', [
            'select0' => $username,
        ]);

        return $response['error'] === '0';
    }

    public function unsuspendAccount(string $username): bool
    {
        $response = $this->makeRequest('UNSUSPEND_USER', [
            'select0' => $username,
        ]);

        return $response['error'] === '0';
    }

    public function deleteAccount(string $username): bool
    {
        $response = $this->makeRequest('DELETE_USER', [
            'select0' => $username,
            'confirmed' => 'yes',
        ]);

        return $response['error'] === '0';
    }

    private function makeRequest(string $command, array $params): array
    {
        $url = "{$this->baseUrl}/CMD_{$command}";
        
        try {
            $response = $this->client->request('POST', $url, [
                'form_params' => $params,
                'auth' => [$this->username, $this->password],
            ]);

            parse_str($response->getBody(), $result);
            return $result;
        } catch (Exception $e) {
            throw new Exception("DirectAdmin API request failed: " . $e->getMessage());
        }
    }
}