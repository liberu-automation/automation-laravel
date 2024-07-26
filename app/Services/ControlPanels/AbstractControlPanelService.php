<?php

namespace App\Services\ControlPanels;

abstract class AbstractControlPanelService
{
    abstract public function createAccount(array $data): array;
    abstract public function suspendAccount(string $accountId): array;
    abstract public function deleteAccount(string $accountId): array;

    protected function formatResponse(bool $success, string $message, array $data = []): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
    }
}