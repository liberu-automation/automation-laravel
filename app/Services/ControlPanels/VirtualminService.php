<?php

namespace App\Services\ControlPanels;

use Illuminate\Support\Facades\Http;

class VirtualminService extends AbstractControlPanelService
{
    private $apiUrl;
    private $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.virtualmin.api_url');
        $this->apiKey = config('services.virtualmin.api_key');
    }

    public function createAccount(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/create-account', $data);

            if ($response->successful()) {
                return $this->formatResponse(true, 'Account created successfully', $response->json());
            } else {
                return $this->formatResponse(false, 'Failed to create account', $response->json());
            }
        } catch (\Exception $e) {
            return $this->formatResponse(false, 'Error creating account: ' . $e->getMessage());
        }
    }

    public function suspendAccount(string $accountId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->post($this->apiUrl . '/suspend-account', ['account_id' => $accountId]);

            if ($response->successful()) {
                return $this->formatResponse(true, 'Account suspended successfully', $response->json());
            } else {
                return $this->formatResponse(false, 'Failed to suspend account', $response->json());
            }
        } catch (\Exception $e) {
            return $this->formatResponse(false, 'Error suspending account: ' . $e->getMessage());
        }
    }

    public function deleteAccount(string $accountId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->delete($this->apiUrl . '/delete-account', ['account_id' => $accountId]);

            if ($response->successful()) {
                return $this->formatResponse(true, 'Account deleted successfully', $response->json());
            } else {
                return $this->formatResponse(false, 'Failed to delete account', $response->json());
            }
        } catch (\Exception $e) {
            return $this->formatResponse(false, 'Error deleting account: ' . $e->getMessage());
        }
    }
}