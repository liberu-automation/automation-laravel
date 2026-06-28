# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repo layout note

The Laravel application and its git repository live in `src/` (this directory), not the
parent `liberu-automation/` folder. Run all commands from here.

## Commands

```bash
# First-time setup (interactive: env, db, composer, migrate --seed, tests, serve)
./setup.sh

# Manual setup
composer install && php artisan key:generate && php artisan migrate --seed

# Dev — run both in parallel
php artisan serve            # PHP server
npm run dev                  # Vite (Tailwind 4 + Flowbite/Preline assets)
php artisan pail             # tail logs

# Tests (PHPUnit, sqlite :memory:, suites: Unit + Feature)
./vendor/bin/phpunit
./vendor/bin/phpunit --filter ModuleSystemTest        # single class/method
./vendor/bin/phpunit tests/Feature/UserTest.php       # single file

# Format (PSR-12) — run before committing
./vendor/bin/pint

# Module management (see Architecture)
php artisan module list|info <name>|enable <name>|disable <name>|install <name>|uninstall <name>|create <name>

# Sail (Docker Compose) alternative
./vendor/bin/sail up -d
```

`rector.php` is present for automated refactors but Rector is not in `composer.json` —
install it before invoking `./vendor/bin/rector`.

## Stack

Laravel 13 · PHP 8.5 · Filament 5 · Livewire 4 · Jetstream + Fortify + Socialstream
(team-based auth, social login) · Sanctum (API) · Filament Shield (roles/permissions).
`minimum-stability: beta` — expect pre-release framework packages.

## Architecture

### Two Filament panels, both team-tenant scoped
- `app/Providers/Filament/AppPanelProvider.php` → `/app` (default panel, public registration).
- `app/Providers/Filament/AdminPanelProvider.php` → `/admin` (Filament Shield, no registration).
- Both call `->tenant(Team::class, ownershipRelationship: 'team')` and add the
  `TeamsPermission` auth middleware. **A Filament resource that is not team-scoped will 500
  on every page unless it opts out of tenancy** (`protected static bool $isScopedToTenant = false;`).
- Resource/page discovery paths differ per panel. The admin panel discovers resources from
  **both** `app/Filament/Resources` and `app/Filament/Admin/Resources`; the app panel discovers
  from `app/Filament/App/Resources`. Put a new resource in the directory matching the panel
  that should see it.

### Module system (`app/Modules/`)
Pluggable feature modules managed by `ModuleManager`, driven by the `module` artisan command.
`ModuleManager::loadModules()` scans **two** locations:
- **Legacy** `app/Modules/<Name>/` — a `<Name>Module` class extending `BaseModule`, plus a
  `module.json` (name, version, description, dependencies, config). `module create` scaffolds this.
- **Modern** `app-modules/` — internachi/modular pattern, `Modules\` namespace, wrapped in an
  anonymous `ModuleInterface` adapter.

Each discovered module is mirrored into the `Module` Eloquent model (DB) for persisted
enabled-state and metadata. Lifecycle: `install` runs the module's migrations + publishes
assets + enables; `enable`/`disable`/`install`/`uninstall` fire `App\Modules\Events\*` events
and call `onEnable`/`onDisable`/`onInstall`/`onUninstall` hooks. `ModuleManager` enforces
dependency checks (cannot enable with unmet deps, cannot disable/uninstall with dependents) and
caches the module list (config in `config/modules.php`; disable via `modules.development`).
Note the enabled flag lives in **two** places — `BaseModule` reads `Cache`, the `Module` row
stores `enabled` — keep them in sync when changing module state logic.

### Hosting automation services (`app/Services/`)
`WebHostingControlPanelManager` is the entry point for control-panel automation. Constructed
with a panel name (`virtualmin` | `cpanel` | `plesk` | `directadmin`), it instantiates the
matching `*ApiClient` from `config('services.<panel>.*')` and dispatches account operations
(create/suspend/etc.) by `switch` on the panel name. Add a new panel by adding a client class,
a config block, and the matching cases.

### Auth actions (`app/Actions/`)
Jetstream/Fortify/Socialstream customization lives here (`CreateNewUserWithTeams`,
`CreateTeam`, `ResolveSocialiteUser`, etc.) — edit these rather than vendor files to change
registration, team, or social-login behavior.

See `docs/MODULAR_ARCHITECTURE.md` for the fuller module design writeup.
```
