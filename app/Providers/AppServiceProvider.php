<?php

namespace App\Providers;

use App\Listeners\SyncHostingWithSubscription;
use App\Models\Team;
use App\Modules\ModuleManager;
use App\Modules\ModuleServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookHandled;

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

        // Provision/suspend a Team's hosting when its subscription state changes.
        Event::listen(WebhookHandled::class, SyncHostingWithSubscription::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
