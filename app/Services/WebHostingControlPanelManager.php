<?php

namespace App\Services;

use Exception;

class WebHostingControlPanelManager
{
    private $controlPanel;
    private $virtualminClient;
    private $directAdminClient;

    public function __construct(string $controlPanel)
    {
        $this->controlPanel = $controlPanel;
        if ($controlPanel === 'virtualmin') {
            $this->virtualminClient = new VirtualminApiClient(
                config('services.virtualmin.base_url'),
                config('services.virtualmin.username'),
                config('services.virtualmin.password')
            );
        } elseif ($controlPanel === 'directadmin') {
            $this->directAdminClient = new DirectAdminApiClient(
                config('services.directadmin.base_url'),
                config('services.directadmin.username'),
                config('services.directadmin.password')
            );
        }
    }

    public function createAccount(array $data): bool
    {
        switch ($this->controlPanel) {
            case 'virtualmin':
                return $this->createVirtualminAccount($data);
            case 'cpanel':
                return $this->createCpanelAccount($data);
            case 'plesk':
                return $this->createPleskAccount($data);
            case 'directadmin':
                return $this->createDirectAdminAccount($data);
            default:
                throw new Exception("Unsupported control panel: {$this->controlPanel}");
        }
    }

    public function suspendAccount(string $accountId): bool
    {
        switch ($this->controlPanel) {
            case 'virtualmin':
                return $this->suspendVirtualminAccount($accountId);
            case 'cpanel':
                return $this->suspendCpanelAccount($accountId);
            case 'plesk':
                return $this->suspendPleskAccount($accountId);
            case 'directadmin':
                return $this->suspendDirectAdminAccount($accountId);
            default:
                throw new Exception("Unsupported control panel: {$this->controlPanel}");
        }
    }

    public function unsuspendAccount(string $accountId): bool
    {
        switch ($this->controlPanel) {
            case 'virtualmin':
                return $this->unsuspendVirtualminAccount($accountId);
            case 'cpanel':
                return $this->unsuspendCpanelAccount($accountId);
            case 'plesk':
                return $this->unsuspendPleskAccount($accountId);
            case 'directadmin':
                return $this->unsuspendDirectAdminAccount($accountId);
            default:
                throw new Exception("Unsupported control panel: {$this->controlPanel}");
        }
    }

    public function deleteAccount(string $accountId): bool
    {
        switch ($this->controlPanel) {
            case 'virtualmin':
                return $this->deleteVirtualminAccount($accountId);
            case 'cpanel':
                return $this->deleteCpanelAccount($accountId);
            case 'plesk':
                return $this->deletePleskAccount($accountId);
            case 'directadmin':
                return $this->deleteDirectAdminAccount($accountId);
            default:
                throw new Exception("Unsupported control panel: {$this->controlPanel}");
        }
    }

    // Implement methods for each control panel (Virtualmin, cPanel, Plesk, DirectAdmin)
    // Example for Virtualmin:

    private function createVirtualminAccount(array $data): bool
    {
        return $this->virtualminClient->createAccount($data);
    }

    private function suspendVirtualminAccount(string $accountId): bool
    {
        return $this->virtualminClient->suspendAccount($accountId);
    }

    private function unsuspendVirtualminAccount(string $accountId): bool
    {
        return $this->virtualminClient->unsuspendAccount($accountId);
    }

    private function deleteVirtualminAccount(string $accountId): bool
    {
        return $this->virtualminClient->deleteAccount($accountId);
    }

    // Implement similar methods for cPanel, Plesk, and DirectAdmin

    private function createDirectAdminAccount(array $data): bool
    {
        return $this->directAdminClient->createAccount($data);
    }

    private function suspendDirectAdminAccount(string $accountId): bool
    {
        return $this->directAdminClient->suspendAccount($accountId);
    }

    private function unsuspendDirectAdminAccount(string $accountId): bool
    {
        return $this->directAdminClient->unsuspendAccount($accountId);
    }

    private function deleteDirectAdminAccount(string $accountId): bool
    {
        return $this->directAdminClient->deleteAccount($accountId);
    }
}