<?php

namespace App\Modules;

use App\Models\Module;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ($this->moduleDirectories() as $modulePath) {
            $this->registerModule(basename($modulePath), $modulePath);
        }
    }

    public function boot(): void
    {
        foreach ($this->moduleDirectories() as $modulePath) {
            $this->bootModule(basename($modulePath), $modulePath);
        }
    }

    protected function moduleDirectories(): array
    {
        $dirs = [];

        $legacyPath = app_path('Modules');
        if (File::exists($legacyPath)) {
            foreach (File::directories($legacyPath) as $path) {
                $dirs[] = $path;
            }
        }

        $modularPath = base_path(config('modular.modules_directory', 'app-modules'));
        if (File::exists($modularPath)) {
            foreach (File::directories($modularPath) as $path) {
                $dirs[] = $path;
            }
        }

        return $dirs;
    }

    protected function registerModule(string $moduleName, string $modulePath): void
    {
        // Register own service provider first (always, so it can bind things)
        $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";
        if (class_exists($providerClass)) {
            $this->app->register($providerClass);
        }

        // Config is always merged so it is available even when disabled
        $configPath = "{$modulePath}/config";
        if (File::exists($configPath)) {
            foreach (File::files($configPath) as $configFile) {
                $key = Str::snake($moduleName) . '.' . $configFile->getFilenameWithoutExtension();
                $this->mergeConfigFrom($configFile->getPathname(), $key);
            }
        }

        if (! $this->isModuleEnabled($moduleName)) {
            return;
        }

        // Routes (only for enabled modules)
        $routesPath = "{$modulePath}/routes";
        if (File::exists($routesPath)) {
            foreach (['web.php', 'api.php', 'admin.php'] as $file) {
                $path = "{$routesPath}/{$file}";
                if (File::exists($path)) {
                    $this->loadRoutesFrom($path);
                }
            }
        }

        // Views
        $viewsPath = "{$modulePath}/resources/views";
        if (File::exists($viewsPath)) {
            $this->loadViewsFrom($viewsPath, Str::snake($moduleName));
        }

        // Translations
        $langPath = "{$modulePath}/resources/lang";
        if (File::exists($langPath)) {
            $this->loadTranslationsFrom($langPath, Str::snake($moduleName));
        }

        // Migrations
        $migrationsPath = "{$modulePath}/database/migrations";
        if (File::exists($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function bootModule(string $moduleName, string $modulePath): void
    {
        // Assets and config are always publishable regardless of enabled state
        $assetsPath = "{$modulePath}/resources/assets";
        if (File::exists($assetsPath)) {
            $this->publishes(
                [$assetsPath => public_path("modules/{$moduleName}")],
                Str::snake($moduleName) . '-assets'
            );
        }

        $configPath = "{$modulePath}/config";
        if (File::exists($configPath)) {
            foreach (File::files($configPath) as $configFile) {
                $this->publishes(
                    [$configFile->getPathname() => config_path(Str::snake($moduleName) . '.' . $configFile->getFilename())],
                    Str::snake($moduleName) . '-config'
                );
            }
        }
    }

    protected function isModuleEnabled(string $moduleName): bool
    {
        try {
            $module = Module::where('name', $moduleName)->first();

            return $module ? (bool) $module->enabled : true;
        } catch (\Throwable) {
            return true;
        }
    }
}