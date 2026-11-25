# Testing

The suite runs on Pest 4 (PHPUnit underneath) against PostgreSQL. It has more than 1,200 tests. The
tests check what the hospital relies on:

- the rules of [domain.md](domain.md), through HTTP and a real database
- the permission of every route, and what each role sees
- the concurrency guards that can be reproduced in one process (repeated payments, double submits,
  double clicks)
- that every page renders over realistic demo data
- that the code keeps the layout of [folder-layout.md](folder-layout.md)

## Layout

`phpunit.xml` defines three suites: `Unit`, `Feature` and `Arch`.

```
tests/
  Pest.php                  Feature tests: Tests\TestCase + RefreshDatabase; the adminWithPermissions() helper
  TestCase.php              withoutVite() (pages render without a front-end build), no sign-in
                            timebox, and be() forgetting the session's password hash on a switch of account
  Support/DashboardRoutes.php   the dashboard routes and the permissions their middleware requires
  Arch/ArchitectureTest.php     architecture rules (Pest arch presets and the folder layout)
  Unit/
    Billing/                Amount, CurrencyTotals, BillableType
    Clinical/               DocumentScoreCalculator, scale by scale
    Enums/                  the lifecycles (shape of every nextStatuses()), labels, badges, icons
    Patients/  Scheduling/  days in hospital; SlotGrid and TimeRange
  Feature/
    Billing/  Clinical/  Patients/  Scheduling/  Stock/  Dashboard/
                            one folder per module: behaviour through HTTP and the database
    Authorization/          every route's permission, page access per role, menu, action permissions
    Security/               sign-in, CSRF, headers, malformed input, patient files, HTTP verbs, /api access
    Database/               enum CHECK constraints, morph map, relations, factories, seeders, restricted deletes
    Frontend/               page rendering and smoke tests over the demo data, layout, navigation, Vite entries
    SmokeTest.php           health check, login form, guest redirect
    StatusComponentsTest.php    <x-status-badge> and <x-status-icon>
  js/                       Vitest, not Pest: the page scripts (lib/, pages/; support/ builds the booking page)
```

- **Unit tests** cover logic that needs neither the framework nor a database: money, scoring
  scales, time ranges and slot grids, and the enums. They run without the Laravel application.
- **Feature tests** go through HTTP (`get()`, `post()`, `putJson()`...) or call a service directly
  when the HTTP layer adds nothing. They assert on the response and on the database. A module's
  tests sit in that module's folder, and the cross-cutting checks have folders of their own.
- **Arch tests** (`tests/Arch/ArchitectureTest.php`) hold the code base to its conventions:
  - the Pest `php` and `security` presets
  - no `dd()` / `dump()` / `ray()`
  - `env()` only in config files, the files without a namespace included
  - controllers, form requests, models and exceptions live in their namespaces, and classes named
    `*Query` live only in `App\Queries` (the other read models there are placed by convention);
    controllers, form requests and exceptions carry their suffix; models extend Eloquent's
    `Model`, form requests `FormRequest`, exceptions `DomainException`
  - every enum is string-backed and has a label
  - services and read models do not use the HTTP layer or the session; services take the acting
    admin as a parameter, and only `StockLedger` reads the signed-in one (the author of a stock
    movement)
  - Blade components and the menu do not query models
- **Page script tests** (`tests/js`, `npm test`) run the JavaScript of `resources/js` with Vitest
  in happy-dom, a DOM without a browser (`vitest.config.js`; `tests/js/setup.js` gives it the
  page's jQuery). They cover the shared modules (times and weeks, escaping, form rows, `fetchJson`
  and the expired session, the patient picker) and the two booking grids, loaded over a canned page
  and stubbed endpoints. The other page scripts are only parsed and built.

## Helpers

| Helper | What it does |
|---|---|
| `adminWithPermissions(string ...$permissions)` (`tests/Pest.php`) | An admin account holding exactly these permissions. They are created on the fly for the `admin` guard, so a test does not need the whole `PermissionSeeder`. |
| `Admin::factory()->mainAdmin()`, `->role($name)`, `->forStaff()` | An account with the `main_admin` role, or with another role by name such as `Регистратор` (found or created; a test seeds `PermissionSeeder` first when the role needs its default permissions), or the login of an employee. |
| `Tests\Support\DashboardRoutes` | The routes under `/dashboard` and `/api` (`all()`, `parameterlessPages()`), the permissions their middleware requires (`permissionsOf()`, `allows()`), and URLs filled with factory-made or existing records (`urlWithFactoryRecords()`, `urlWithFirstRecords()`). `AUTHENTICATED_ONLY` lists the one route without a permission. The authorization and page-access tests and the page sweep of `Frontend/AllPagesTest.php` are built on it, so a new route is covered without writing a test for it. |
| `Tests\Feature\Scheduling\SchedulingFixtures` | A bookable doctor (appointment length, reception hours on the weekday, an appointment service), appointments, and booking payloads. The logins of the calendar rules: `doctorAccount()`, the dataset `doctorAccounts()` (a doctor, and a doctor who is also the main administrator), and `account()` with the dataset `WITHOUT_DOCTOR_PROFILE` (no employee, registrar, cashier, nurse). |
| `Tests\Feature\Stock\StockFixtures` | Main-warehouse and department batches with an expiry date, department warehouses of a type, and `movementsOf()` to read the ledger rows of a stock row. |
| `Tests\Feature\Clinical\Support\Spreadsheet` | Builds an `.xlsx` upload for the import tests. |
| `Tests\Feature\Frontend\RenderedPage` | Checks rendered HTML: leftovers of the removed commercial template, CDN hosts, Bootstrap 4 classes and attributes, inline event handlers, `javascript:` links, images without `alt`, form controls without a label, buttons and links without an accessible name (a bare symbol such as "<" is none), `POST` forms without the CSRF token, buttons without a `type` inside a form, ids used twice, headings that skip a level, and dates or decimals printed raw (`rawValues()`). It also parses every inline script with Node (`parse-scripts.mjs`). |
| Factories and seeders | The models the tests create have factories with states for the common cases (`Staff::factory()->doctor()`, `Service::factory()->appointment()`). `Database/FactoriesTest.php` persists a valid record of each. The page sweeps seed `DatabaseSeeder` and `DemoSeeder`, so pages render over a full demo hospital. |

Some patterns recur across the suite:

- **Fixed query counts.** The tests of the patient card and list, the stay edit form, the cashier
  list, the admin and staff lists, the nurse window and the dashboard count the queries for small
  and large data. They fail if an eager load is dropped.
- **Constraint tests use a savepoint.** A failed statement aborts the surrounding PostgreSQL
  transaction that `RefreshDatabase` opened. A test that expects the database to refuse a write
  (the overlap constraint, the one-open-stay index, a restricted delete) runs that write in a
  nested transaction (a savepoint), asserts the SQLSTATE or the message, and rolls the savepoint
  back.
- **Regression tests are named for the behaviour,** for example "refuses a repeated payment request
  instead of applying it twice". Many tests started as the failing reproduction of a bug found in
  the 1.x code.

## The database: PostgreSQL only

The tests run on PostgreSQL 18, never on SQLite. The rules under test live partly in PostgreSQL:

- the `receptions_no_overlap` exclusion constraint (GiST over ranges)
- the partial unique index on open stays
- the CHECK constraints of enum and quantity columns
- `ILIKE` searches over Cyrillic names
- `SELECT ... FOR UPDATE` and PostgreSQL's re-check of an `UPDATE` condition on rows changed
  concurrently
- window functions and `count(*) filter (...)` in the read models
- the SQLSTATEs the code maps to messages (23P01 for an overlap, 23503 and 23001 for a restricted
  delete, 22P02 and 22003 for a malformed id in a URL), and the one it keeps from happening
  (22021, bytes that are not UTF-8)

The PostgreSQL-specific migrations skip themselves on other drivers. On SQLite the schema would
build without the constraints, and the tests that assert them would fail. `phpunit.xml` therefore
defaults to `DB_CONNECTION=pgsql` and `DB_DATABASE=medsystem_testing`.

**The test database is wiped.** Feature tests use `RefreshDatabase`. The first test of a run
migrates the configured database from scratch, which drops every table in it, and each test then
runs in a transaction that is rolled back. Never point `DB_DATABASE` at a database whose data you
need.

Environment variables you set yourself override the `DB_*` defaults of `phpunit.xml`. The other
variables are forced for every run: `APP_ENV=testing`, the `array` cache and session drivers, the
`sync` queue, the `array` mailer, `BCRYPT_ROUNDS=4`, Telescope off and
`APP_TIMEZONE=Asia/Tashkent`.

| Where | How the test database is set up |
|---|---|
| Docker | `docker/postgres/init/01-create-testing-database.sql` creates `medsystem_testing` next to the development database. `make test` runs `composer test` in the `app` container with `DB_DATABASE=medsystem_testing`. |
| A local PostgreSQL | Create an empty database with a UTF-8 locale and run, for example, `DB_DATABASE=medsystem_testing DB_USERNAME=... DB_PASSWORD=... vendor/bin/pest`. |
| CI | The `postgres:18-alpine` service creates `medsystem_testing`, and the job sets the `DB_*` variables. |

The locale matters. `ILIKE` and `lower()` fold the case of Cyrillic letters only under a locale that
knows them: a libc `*.UTF-8` locale, or PostgreSQL's builtin `C.UTF-8` locale (PostgreSQL 17 and
later). Under the plain `C` locale `lower('Ж')` stays `Ж`, and the Cyrillic search tests fail. A
database with the builtin locale is created like this:

```sql
CREATE DATABASE medsystem_testing TEMPLATE template0 LOCALE_PROVIDER builtin BUILTIN_LOCALE 'C.UTF-8';
```

## Parallel runs

```bash
vendor/bin/pest --parallel                  # one process per CPU core
vendor/bin/pest --parallel --processes=4
```

- Laravel creates one database per process, named `<DB_DATABASE>_test_<N>`, and migrates it once.
  Those databases are created from the server's `template1`, so `template1` needs such a locale
  too. Otherwise the Cyrillic search tests fail only in parallel runs.
- Rebuilding a dozen test databases at the same moment needs more PostgreSQL locks than the default
  allows. `compose.yaml` starts PostgreSQL with `max_locks_per_transaction=256` for that reason.
- On four processes a full run takes less than half the time of a sequential one.

## Commands

| Command | What it runs |
|---|---|
| `composer test` / `make test` | `php artisan config:clear`, then `pest` (`make` runs it in the container against `medsystem_testing`) |
| `composer test:coverage` / `make coverage` | the same with `--coverage` (needs pcov or Xdebug) |
| `vendor/bin/pest --testsuite=Arch` | the architecture rules only |
| `composer lint` / `make lint` | `pint --test` (`composer lint:fix` / `make fix` applies the fixes) |
| `composer analyse` / `make analyse` | Larastan, level 8, on `app/`, `bootstrap/app.php`, `database/` and `routes/` (`phpstan.neon.dist`), with no baseline |
| `composer refactor` / `make refactor` | `rector process --dry-run`. Rector must have nothing left to change. |
| `composer ci` / `make ci` | lint, analyse, refactor and test, in that order |

A sequential run holds more than PHP's default 128 or 256 MB by the time the `Arch` suite parses
the code base, so `phpunit.xml` sets `memory_limit` to 1G for every test process.

The front-end smoke tests parse inline scripts with Node. Without Node installed those tests are
marked incomplete instead of passing silently. No test needs a front-end build: `TestCase` calls
`withoutVite()`.

## What CI runs

`.github/workflows/ci.yml` runs on every pull request and on pushes to `main`. A newer push to a
pull request cancels that pull request's running workflow; every push to `main` runs to the end.

| Job | Steps |
|---|---|
| `assets` | `npm ci`, `npm test` (Vitest, `tests/js`), `npm run build`, then `node tests/Feature/Frontend/check-manifest.mjs`: every file the views load must be in the Vite manifest |
| `dependency-audit` | `composer audit --abandoned=report` and `npm audit --omit=dev --audit-level=high` |
| `code-style` | `composer lint` (Pint) |
| `static-analysis` | `vendor/bin/phpstan analyse` (Larastan level 8, no baseline) and `vendor/bin/rector process --dry-run` |
| `tests` (PHP 8.3 and 8.4) | a `postgres:18-alpine` service, `.env` from `.env.example`, `php artisan key:generate`, `php artisan migrate:fresh --seed --force` (the migrations and every seeder must run on an empty database; `.env.example` sets `APP_ENV=local`, so `DemoSeeder` runs too), `php artisan migrate:refresh --force` (every `down()` runs over the demo data, then every migration again), then `vendor/bin/pest --parallel --coverage --min=40` with pcov |

## Related decisions

- [ADR 0011](../adr/0011-quality-gates.md): Quality gates
- [ADR 0001](../adr/0001-postgresql-as-the-only-database.md): PostgreSQL as the only database
