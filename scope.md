# Project Scope — liberu-automation

Verification of the README **Main Features** claims against the actual codebase.
Reviewed: 2026-06-28. App lives in `src/`.

Legend: ✅ confirmed · ⚠️ partial / overstated · ❌ not present

| # | README feature | Status | Evidence |
|---|----------------|--------|----------|
| 1 | Hosting control panel | ✅ | `WebHostingAccount` model + full Filament resource (`app/Filament/Resources/WebHostingAccounts/` — List/Create/Edit). User/Team/SiteSettings/ConnectedAccount models + resources present. |
| 2 | Billing automation | ❌ | **Not found.** No billing/invoice/payment/stripe/subscription code in `app/`, `database/`, `routes/`. No webhook/workflow/trigger hooks. Seeders are Menu/Permissions/Roles/SiteSettings/Team/User only. Listeners are email/team, not payment. |
| 3 | Filament 5 admin resources | ✅ | Three panels: `app/Filament/{Admin,App,Resources}`. Admin resources: `SiteSettings`, `Menu`, `Users`. Filament `^5.6.7`. |
| 4 | Livewire 4 components | ⚠️ | Livewire `^4.3.3` present and Filament rides on it, but only **one** custom component: `app/Http/Livewire/CreateTeam.php`. "components" (plural, custom reactive UI) overstates what's hand-built. |
| 5 | Pluggable modules architecture | ✅ | `app/Modules/`: `ModuleManager`, `ModuleServiceProvider`, `BaseModule`, `BlogModule`, `Events/`, `Contracts/`, `Traits/`. `config/modules.php`, `modules` table, `Module` model, `tests/Feature/ModuleSystemTest.php`. Wired via `AppServiceProvider`. |
| 6 | Docker & Sail ready | ✅ | `Dockerfile`, `docker-compose.yml`, `laravel/sail` dev dep. |
| 7 | CI/CD workflows | ✅ | `.github/workflows/`: `install.yml`, `main.yml`, `security.yml`, `tests.yml` + `.circleci/config.yml`. |
| 8 | Comprehensive test suite | ⚠️ | Codecov integration confirmed (`install.yml`, `tests.yml`). But only ~6 test files, 2 of them are `ExampleTest` stubs — effective coverage is `ModuleSystemTest`, `UserTest`, `SocialstreamConfigTest`. "Comprehensive" overstates. |

## Summary

- **Confirmed (5):** Hosting control panel, Filament admin resources, Pluggable modules, Docker/Sail, CI/CD workflows.
- **Overstated (2):** Livewire components (1 custom), Test suite (minimal, has stubs).
- **Not present (1):** Billing automation — no payment/invoice/workflow code exists.

## Recommendation

README's "Billing automation" line should be removed or marked roadmap until billing code lands. Soften "Livewire 4 components" and "Comprehensive test suite" to match reality, or build them out.

Prioritized backlog, dependencies, and out-of-scope boundaries moved to `TASKS.md`.

---

## Note (out of scope of feature review)

Security audit applied separately (PR #454): tenant-isolation fix, encrypted hosting
credentials, CORS/headers/HTTPS hardening, dead-code removal. Outstanding manual task:
rotate prod `APP_KEY` (leaked in git history) — tracked as **T1** in `TASKS.md`.
