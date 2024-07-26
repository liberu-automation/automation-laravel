<?php

namespace App\Filament\Resources\WebHostingAccountResource\Pages;

use App\Filament\Resources\WebHostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebHostingAccount extends EditRecord
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}