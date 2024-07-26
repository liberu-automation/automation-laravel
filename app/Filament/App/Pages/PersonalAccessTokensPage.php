<?php

namespace App\Filament\App\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PersonalAccessTokensPage extends Page
{
    protected static string $view = 'filament.pages.profile.personal-access-tokens';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Account';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Personal Access Tokens';

    public User $user;

    public function mount(): void
    {
        $this->user = Auth::user();
    }

    public function createApiToken(string $name, array $abilities = []): void
    {
        $this->user->createToken($name, $abilities);
    }

    public function deleteApiToken(string $name): void
    {
        $this->user->tokens()->where('name', $name)->first()->delete();
    }

    public function getHeading(): string
    {
        return static::$title;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getApiScopes(): array
    {
        return [
            'control-panel:create' => 'Create hosting accounts',
            'control-panel:suspend' => 'Suspend hosting accounts',
            'control-panel:delete' => 'Delete hosting accounts',
        ];
    }
}