<?php

namespace App\Filament\Resources\WebHostingAccountResource\Pages;

use App\Filament\Resources\WebHostingAccountResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebHostingAccounts extends ListRecords
{
    protected static string $resource = WebHostingAccountResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}