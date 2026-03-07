<?php

namespace App\Filament\Resources\WebHostingAccounts\Pages;

use App\Filament\Resources\WebHostingAccounts\WebHostingAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebHostingAccount extends EditRecord
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
