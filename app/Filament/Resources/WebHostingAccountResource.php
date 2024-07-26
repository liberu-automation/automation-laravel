<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebHostingAccountResource\Pages;
use App\Models\WebHostingAccount;
use App\Services\WebHostingControlPanelManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;

class WebHostingAccountResource extends Resource
{
    protected static ?string $model = WebHostingAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('control_panel')
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
                Tables\Columns\TextColumn::make('domain'),
                Tables\Columns\TextColumn::make('username'),
                Tables\Columns\TextColumn::make('control_panel'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->action(fn (WebHostingAccount $record) => static::suspendAccount($record))
                    ->requiresConfirmation(),
                Tables\Actions\DeleteAction::make()
                    ->action(fn (WebHostingAccount $record) => static::deleteAccount($record)),
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
            'index' => Pages\ListWebHostingAccounts::route('/'),
            'create' => Pages\CreateWebHostingAccount::route('/create'),
            'edit' => Pages\EditWebHostingAccount::route('/{record}/edit'),
        ];
    }

    protected static function createAccount(array $data): bool
    {
        $manager = new WebHostingControlPanelManager($data['control_panel']);
        return $manager->createAccount($data);
    }

    protected static function suspendAccount(WebHostingAccount $account): bool
    {
        $manager = new WebHostingControlPanelManager($account->control_panel);
        return $manager->suspendAccount($account->id);
    }

    protected static function deleteAccount(WebHostingAccount $account): bool
    {
        $manager = new WebHostingControlPanelManager($account->control_panel);
        return $manager->deleteAccount($account->id);
    }
}
