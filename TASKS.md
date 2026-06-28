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
- [ ] **T3. Fix README accuracy.** (PR-T3) Remove "Billing automation" (or mark *roadmap*); soften "Livewire 4 components" and "Comprehensive test suite".
  → T8 (decided: roadmap-only, so T3 ships now). Docs only — no test.

## P1 — Billing automation — Stripe via Laravel Cashier

Largest gap: claimed in README, entirely absent. Greenlit 2026-06-28. Use `laravel/cashier`
(Stripe) — handles subscriptions, invoices, webhook signature verify, `Billable` trait —
over hand-rolled PaymentIntents.

Billing entity = **Team** (panels are Team-tenant scoped). Make `Team` billable, not `User`.

- [x] **T8. GATE — decided 2026-06-28: build now with Stripe + Cashier.** README updates from *roadmap* to shipped once T7 lands (revisit T3).
- [ ] **T4. Install + config Cashier.** (PR-T4) `composer require laravel/cashier`, publish + run migrations (`customer`/`subscription` columns), set `STRIPE_KEY`/`STRIPE_SECRET`/`STRIPE_WEBHOOK_SECRET` in `.env` + `config/services.php`. Add `Billable` to `Team` model, set `Cashier::useCustomerModel(Team::class)`.
  Tests first: `Team` is Billable, creates Stripe customer (Cashier fake/`stripe-mock`), migrations present.
  → **T2 merged first** (tenancy correct before money touches data). → T1 (key rotated).
- [ ] **T5. Plans + subscriptions.** (PR-T5) Define Stripe products/prices (hosting tiers). Subscribe flow per Team (`$team->newSubscription(...)->create($paymentMethod)`). Filament resource/page under App panel for plan select + payment method (Stripe Payment Element).
  Tests first: subscribe creates active subscription, swap/cancel, tenant can't bill another Team.
  → T4.
- [ ] **T6. Webhooks.** (PR-T6) Register Cashier webhook route (`cashier:webhook` or mounted controller), set endpoint in Stripe dashboard, verify signature via `STRIPE_WEBHOOK_SECRET`. Handle subscription lifecycle (created/updated/deleted, payment failed). Don't trust client redirects for fulfillment.
  Tests first: valid signature processed, bad signature 403, each event mutates state correctly.
  → T4.
- [ ] **T7. Tie billing → hosting actions.** (PR-T7) On subscription active → provision/un-suspend via `WebHostingControlPanelManager`; on past_due/canceled → suspend. Hook into the module `Events/` or webhook handlers.
  Tests first: active→provision called, past_due/canceled→suspend called (mock the panel manager).
  → T5, T6.

## P2 — Quality

- [ ] **T9. Expand test suite.** (PR-T9) Delete `ExampleTest` stubs (Feature + Unit); add coverage for hosting CRUD, tenant access gating, module system, auth. (This PR *is* the tests.)
  → T2 (assert the *fixed* gating, not the bypass).
- [ ] **T10. Livewire components.** (PR-T10) Build the reactive UI README implies, or drop the plural claim (folded into T3).
  Tests first (if building): Livewire component test per interaction.
  → none.

## P3 — Housekeeping

- [ ] **T11. Empty `docker-compose.dev.yml` (2 bytes)** (PR-T11) in umbrella dir — fill with real dev override or delete. Config only — no test.
  → none.
- [ ] **T12. Enable passkeys properly.** (PR-T12) Repo half-shipped passkeys (migration + `config/fortify.php` + `passkeys` rate limiter) but enabling deps absent (broke boot/CI until disabled). To enable: upgrade `laravel/fortify` to `^2` (adds `Features::passkeys()`), install `laravel/passkeys`, un-comment the two `ponytail:` sites — `config/fortify.php` and `database/migrations/2024_01_01_000000_create_passkeys_table.php` (restore `Passkeys::userModel()`). Verify Jetstream/Fortify v2 compat first.
  Tests first: passkey register + login flow.
  → none.

## Critical path

`T1 + T2` (deploy together) → unblocks `T9` and all of P1.
P1 billing: `T4 → T5,T6 → T7`. Revisit `T3` (README: roadmap → shipped) after T7.
`T3` README softening independent + cheap — ship now; flip Billing line again at T7.

PR merge order (one PR each, green before next opens):
`PR-T3` → `PR-T9` → `PR-T4` → (`PR-T5`, `PR-T6` parallel) → `PR-T7` → housekeeping (`PR-T10`–`PR-T12`).
(T1/T2 are the existing security deploy + PR #454, not new PRs here.)

## Out of scope

Separate scoping needed if wanted:

- **History scrub** of leaked `APP_KEY` (BFG / `git filter-repo`) — T1 rotation neutralizes risk; rewriting shared history is separate + disruptive.
- **New features beyond the 8 README claims.**
- **Filament panel UX/redesign** — resources work; polish not scoped.
- **Infra/deploy** (`k8s/` manifests, prod hosting, secrets manager) — beyond app-code scope.
- **Dependency major-version upgrades** — `composer audit` clean; PR #455 only pins constraints.
- **Multi-provider billing / OAuth marketplace** — Stripe via Cashier only; other providers out until requested.
