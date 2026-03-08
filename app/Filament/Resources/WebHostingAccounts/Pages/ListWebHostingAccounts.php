<?php

namespace App\Filament\Resources\WebHostingAccounts\Pages;

use App\Filament\Resources\WebHostingAccounts\WebHostingAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebHostingAccounts extends ListRecords
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
