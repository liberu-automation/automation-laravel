<?php

namespace App\Filament\Resources\WebHostingAccounts\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\WebHostingAccounts\WebHostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebHostingAccounts extends ListRecords
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}