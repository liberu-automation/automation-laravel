<?php

namespace App\Filament\Resources\WebHostingAccounts\Pages;

use App\Filament\Resources\WebHostingAccounts\WebHostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWebHostingAccount extends CreateRecord
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}