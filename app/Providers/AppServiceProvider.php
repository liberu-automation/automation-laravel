<?php

namespace App\Providers;

use App\Models\Team;
use App\Modules\ModuleManager;
use App\Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the module manager as a singleton
        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager;
        });

        // Register the module service provider
        $this->app->register(ModuleServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Billing entity is the Team (Filament panels are Team-tenant scoped), not the User.
        Cashier::useCustomerModel(Team::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
