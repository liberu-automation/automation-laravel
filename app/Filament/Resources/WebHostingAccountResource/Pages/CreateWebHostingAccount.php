<?php

namespace App\Filament\Resources\WebHostingAccountResource\Pages;

use App\Filament\Resources\WebHostingAccountResource;
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