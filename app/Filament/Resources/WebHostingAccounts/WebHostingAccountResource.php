<?php

namespace App\Filament\Resources\WebHostingAccounts;

use App\Filament\Resources\WebHostingAccounts\Pages\CreateWebHostingAccount;
use App\Filament\Resources\WebHostingAccounts\Pages\EditWebHostingAccount;
use App\Filament\Resources\WebHostingAccounts\Pages\ListWebHostingAccounts;
use App\Models\WebHostingAccount;
use App\Services\WebHostingControlPanelManager;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebHostingAccountResource extends Resource
{
    protected static ?string $model = WebHostingAccount::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-server';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Select::make('control_panel')
                    ->options([
                        'virtualmin' => 'Virtualmin',
                        'cpanel' => 'cPanel',
                        'plesk' => 'Plesk',
                        'directadmin' => 'DirectAdmin',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain'),
                TextColumn::make('username'),
                TextColumn::make('control_panel'),
                TextColumn::make('status'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('suspend')
                    ->action(fn (WebHostingAccount $record) => static::suspendAccount($record))
                    ->requiresConfirmation(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebHostingAccounts::route('/'),
            'create' => CreateWebHostingAccount::route('/create'),
            'edit' => EditWebHostingAccount::route('/{record}/edit'),
        ];
    }

    protected static function suspendAccount(WebHostingAccount $account): bool
    {
        $manager = new WebHostingControlPanelManager($account->control_panel);
        return $manager->suspendAccount($account->id);
    }
}
