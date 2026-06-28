# TASKS — liberu-automation

Prioritized from `scope.md` (README feature audit, reviewed 2026-06-28).
Priority order: trust/risk → missing core feature → quality → housekeeping.
`→` = blocking dependency.

## Workflow rules

- **TDD (red→green→refactor).** Every code task: write the failing Pest/PHPUnit test
  first, confirm it fails, then code to green, then refactor. Tests run on sqlite
  `:memory:` (`./vendor/bin/phpunit` or `pest`). Stripe-touching tests use Cashier's
  fakes / `stripe-mock`, never live keys. Run `./vendor/bin/pint` before commit.
- **One phase = one PR.** Each task below maps to a single PR, merged in dependency
  order, each green in CI before the next opens. New PR conventions: label
  `enhancement`, reviewer `curtisdelicata`. Branch off `master`.
- A PR is done only when: failing test written first → passing → `pint` clean →
  CI green → reviewed.

## P0 — Truth & security (do first)

- [ ] **T1. Rotate prod `APP_KEY`.** Leaked in git history → forged cookies / decryptable data. Ops task, no code.
  → none. Until done, all encryption guarantees void.
- [ ] **T2. Merge security PR #454** — tenant isolation, encrypted creds, headers/HTTPS.
  → land with T1 in same deploy window (cast + key rotation together).
- [x] **T3. Scope audit + backlog.** (PR #468) `scope.md` audit + this `TASKS.md`. README billing stays *roadmap* until billing merges to main (then flip). Docs only.

## P1 — Billing automation — Stripe via Laravel Cashier

Largest gap: claimed in README, entirely absent. Greenlit 2026-06-28. Use `laravel/cashier`
(Stripe) — handles subscriptions, invoices, webhook signature verify, `Billable` trait —
over hand-rolled PaymentIntents.

Billing entity = **Team** (panels are Team-tenant scoped). Make `Team` billable, not `User`.

- [x] **T8. GATE — decided 2026-06-28: build now with Stripe + Cashier.** README updates from *roadmap* to shipped once T7 lands (revisit T3).
- [x] **T4. Install + config Cashier.** (PR #469) `laravel/cashier ^16.6`; `Billable` on `Team` + `Cashier::useCustomerModel(Team::class)`; customer columns on `teams`, subs/items keyed `team_id`; Stripe env in `.env.example`. Tests: `CashierSetupTest` (3).
- [x] **T5. Plans + subscriptions.** (PR #470, base #469) `config/billing.php` tiers + `App\Billing\Plan`; Filament App-panel `Billing` page subscribes current Team via **Stripe Checkout** (hosted, not Payment Element — lazier). Tests: `PlanCatalogTest` + `BillingPageTest` (6).
- [x] **T6. Webhooks.** (PR #471, base #470) Cashier auto-registers `POST stripe/webhook` w/ signature verify + lifecycle — **verified, no new code**. Tests: `StripeWebhookTest` (2) — bad sig 403, deleted→canceled.
- [x] **T7. Tie billing → hosting.** (PR #472, base #471) `web_hosting_accounts.team_id` + relations; `App\Billing\HostingSubscriptionSync` (active→unsuspend, past_due/canceled→suspend); `SyncHostingWithSubscription` listener on Cashier `WebhookHandled`. Tests: `HostingSubscriptionSyncTest` (3).

**Done 2026-06-28** — billing stack T4→T7 shipped as stacked PRs, full suite 37 green. Remaining: merge stack to main, then flip README billing line *roadmap → shipped* (T3 follow-up).

## P2 — Quality

- [x] **T9. Expand test suite.** Already satisfied by prior work — `ExampleTest` is now real route smoke tests (not stubs), and coverage exists: `WebHostingAccountTest`, `TenantAccessTest`, `ModuleSystemTest`, `UserTest`, `SecurityHeadersTest`. No-op (verified 2026-06-28, 37 suite green). Add explicit login/registration tests later if Fortify customization grows.
- [x] **T10. Livewire claim.** Satisfied by current README — already reads "Livewire 4 + Filament UI ... plus room for custom components" (no overclaim of many hand-built components). Build reactive UI later if a real feature needs it.

## P3 — Housekeeping

- [x] **T11. Empty `docker-compose.dev.yml` (2 bytes)** — deleted 2026-06-28. Untracked, empty (`\n\n`), referenced nowhere. No PR (not in git).
- [ ] **T12. Enable passkeys — BLOCKED upstream.** Needs Fortify v2 (`Features::passkeys()`), but Fortify v2 is `dev-master`/`2.x-dev` only (no beta/stable → fails `minimum-stability: beta`), **and** Jetstream v5.5.3 pins `laravel/fortify ^1.20` (hard conflict). Verified via `composer require laravel/fortify:^2 --dry-run` 2026-06-28. Revisit when Fortify v2 ships stable + a Jetstream release supports it. Ceiling already documented in the two `ponytail:` comments (`config/fortify.php`, `..._create_passkeys_table.php`).
  → blocked on upstream releases.

## Status (2026-06-28)

- **Done:** T3 (#468), T4 (#469), T9, T10, T11.
- **Open PRs (stacked, merge in order):** T5 (#470) → T6 (#471) → T7 (#472). After they land in main, flip the README billing line *roadmap → shipped*.
- **Blocked:** T12 (Fortify v2 unreleased + Jetstream pin).
- **Not mine (ops/security):** T1 (rotate `APP_KEY`), T2 (security PR #454).

## Out of scope

Separate scoping needed if wanted:

- **History scrub** of leaked `APP_KEY` (BFG / `git filter-repo`) — T1 rotation neutralizes risk; rewriting shared history is separate + disruptive.
- **New features beyond the 8 README claims.**
- **Filament panel UX/redesign** — resources work; polish not scoped.
- **Infra/deploy** (`k8s/` manifests, prod hosting, secrets manager) — beyond app-code scope.
- **Dependency major-version upgrades** — `composer audit` clean; PR #455 only pins constraints.
- **Multi-provider billing / OAuth marketplace** — Stripe via Cashier only; other providers out until requested.
