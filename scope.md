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

---

# Prioritized Backlog

Derived from the gaps above. Priority = trust/risk first, then missing core feature,
then quality. `→` marks a blocking dependency.

## P0 — Truth & security (cheap, do first)

- [ ] **T1. Rotate prod `APP_KEY`.** Leaked key sits in git history; anyone with repo
  access can forge cookies/decrypt data. Ops task, no code.
  → none. Blocks nothing, but until done all encryption guarantees are void.
- [ ] **T2. Merge security PR #454** (tenant isolation, encrypted creds, headers/HTTPS).
  → T1 should land in same deploy window (cast + key rotation together).
- [ ] **T3. Fix README accuracy.** Remove "Billing automation" line (or mark *roadmap*);
  soften "Livewire 4 components" and "Comprehensive test suite" to match reality.
  → depends on T8 decision (build billing vs drop claim).

## P1 — Missing core feature: Billing automation

Largest gap — claimed, entirely absent. Decompose only if the product actually needs it
(T8 gate). All build sub-tasks → **T2 merged** (tenancy correct before money touches data).

- [ ] **T8. GATE: build billing or drop the claim?** Product decision. If *drop* → only
  T3 runs and P1 closes. If *build* → T4–T7 proceed. → none (decision, do first in P1).
- [ ] **T4. Decide payment provider + model.** Stripe is already wired at the org level
  (MCP/plugin present) — likely target. Output: data model (invoices, subscriptions,
  charges) scoped to `Team` tenant. → T8.
- [ ] **T5. Invoice/subscription migrations + Eloquent models.** → T4.
- [ ] **T6. Provider integration + webhook handling** (signature-verified, CSRF-excluded
  webhook route only). → T5.
- [ ] **T7. Workflow triggers** (Jobs/Listeners/Events on payment lifecycle) + seeders.
  → T6.

## P2 — Quality

- [ ] **T9. Expand test suite.** Delete `ExampleTest` stubs (Feature + Unit); add coverage
  for hosting CRUD, tenant access gating, module system, auth. → T2 (assert the *fixed*
  gating, not the bypass).
- [ ] **T10. Livewire components** — either build the reactive UI the README implies, or
  drop the plural claim (folded into T3). → none.

## P3 — Housekeeping

- [ ] **T11. `docker-compose.dev.yml` is empty (2 bytes)** in the umbrella dir — fill with
  a real dev override or delete. → none.

## Critical path

`T1+T2` (deploy together) → unblocks `T9` (real tests) and all of `P1`.
`T8` gate decides whether `P1` runs at all. `T3` is independent and cheap — ship now.

---

# Out-of-Scope Boundaries

This backlog does **not** cover, and these need separate scoping if wanted:

- **History scrub** of the leaked `APP_KEY` (BFG/`git filter-repo`) — rotation (T1)
  neutralizes the risk; rewriting shared history is a separate, disruptive op.
- **New feature work beyond the 8 README claims** — anything not listed in Main Features.
- **Filament panel UX/redesign** — resources exist and work; polish is not scoped here.
- **Infra/deploy** (k8s manifests under `k8s/`, production hosting, secrets manager
  rollout) — beyond app-code scope.
- **Dependency major-version upgrades** — `composer audit` is clean; PR #455 only pins
  constraints. No framework bumps planned here.
- **Multi-provider billing / OAuth marketplace** — if T8 picks Stripe, other providers
  are explicitly out until requested.

---

## Note (out of scope of feature review)

Security audit applied separately (PR #454): tenant-isolation fix, encrypted hosting
credentials, CORS/headers/HTTPS hardening, dead-code removal. Outstanding manual task:
rotate prod `APP_KEY` (leaked in git history) — tracked as **T1** above.
