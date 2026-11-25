# 11. Quality gates: Pest on PostgreSQL, Larastan level 8 without a baseline, Rector, Pint and architecture tests

- Status: Accepted
- Date: 2025-11

## Context

The original repository had no working test suite (`phpunit.xml` had its database lines commented
out, and `migrate:fresh` failed on missing foreign key targets), no static analysis that passed,
and no CI. Many of the defects a review of the 1.x code found were of kinds a tool catches: a method called on a
possibly `null` relation, a status string the column did not admit, a route without a permission,
a query on a column that does not exist.

The refactor touched most files of the application. It needed checks that would hold the result in
place for the next change, not a one-off review.

## Decision

Five gates, each runnable locally through `composer` scripts (`composer ci` runs lint, analyse,
refactor and test) and each a job in `.github/workflows/ci.yml`:

| Gate | Tool and configuration | Fails when |
|---|---|---|
| Tests | Pest 4 (`phpunit.xml`): suites `Unit`, `Feature` and `Arch`; `Feature` tests use `RefreshDatabase` on PostgreSQL | a test fails, or line coverage of `app/` is under 40% in CI |
| Static analysis | Larastan 3 at level 8 (`phpstan.neon.dist`) over `app/`, `bootstrap/app.php`, `database/` and `routes/`, with `parseModelCastsMethod: true` | any error; there is no baseline and no `@phpstan-ignore` |
| Automated refactoring | Rector 2 with `driftingly/rector-laravel` (`rector.php`): PHP 8.3 sets, dead code, code quality, type declarations and the Laravel sets up to 13 | `rector process --dry-run` would change a file |
| Code style | Pint (`pint.json`): the Laravel preset plus import, ordering and trailing-comma rules; `.gitattributes` forces LF | `pint --test` would change a file |
| Dependencies | `composer audit --abandoned=report`, `npm audit --omit=dev --audit-level=high` | a security advisory in a Composer package or in an npm package that reaches the browser |

The CI test job first runs `migrate:fresh --seed` on an empty `postgres:18-alpine` database, so the
migrations and seeders are tested on every push, then `migrate:refresh`, so every `down()` runs over
the demo data, and runs the suite on PHP 8.3 and 8.4. A separate job runs the page script tests
(Vitest) and builds the front end ([ADR 10](0010-tabler-and-vite-instead-of-a-commercial-template.md)).

How the tests are written:

- **On the real database.** The suite runs on PostgreSQL, not SQLite, because the constraints,
  locks and error codes of [ADR 1](0001-postgresql-as-the-only-database.md) are part of the
  behaviour under test. `phpunit.xml` forces `APP_TIMEZONE=Asia/Tashkent`, so "today" in a test is
  the hospital's day whatever a local `.env` says.
- **By module**, mirroring [ADR 2](0002-workflow-services-read-models-and-form-requests.md):
  `tests/Feature/<Module>/` through HTTP and the database, `tests/Unit/<Module>/` for logic without
  them; cross-cutting checks in `Authorization`, `Security`, `Database` and `Frontend`.
- **Rules as tests.** `tests/Arch/ArchitectureTest.php` holds the folder layout and Pest's `php` and
  `security` presets; `RouteAuthorizationTest` holds the permission on every route; `EnumColumnsTest`
  holds the `CHECK` constraints equal to the enums; fixed-query-count tests hold the eager loads of
  the heavier pages.
- **A fix comes with a test that failed before it.** During the 2.0.0 refactor each fix was checked
  by reverting it and watching its new test fail.
- **Seed only what a test uses.** Most tests create their own rows with factories and
  `adminWithPermissions()` in `tests/Pest.php`; `PermissionSeeder` inserts its permissions in one
  statement, so the suites that do need the reference data stay fast.

Larastan reads the casts (`parseModelCastsMethod`), so enum, date and decimal attributes have their
cast's type; models also document them with `@property` tags, which were compared once against the
casts and the columns' nullability during the refactor. Every Rector rule that is skipped carries a one-line reason in
`rector.php`, most of them because the rewrite would change behaviour (`==` to `===` between numeric
strings, `throw_if()` moving the exception's origin, stored class names in migrations).

## Consequences

### Positive

- More than 1,200 tests state the hospital's rules as plain sentences ("refuses a repeated payment
  request instead of applying it twice"), and the suite is what lets the code change without a
  manual regression pass.
- Level 8 with no baseline means every reported error is new; there is no list of tolerated
  problems to grow.
- Formatting and mechanical modernisation are not review topics: a file either passes the Rector
  and Pint dry runs or it does not.
- The migrations, the seeders and the front-end build are checked from scratch on every push.

### Negative

- Every test run needs a PostgreSQL server, and a Feature test costs a transaction on it; the full
  suite takes minutes, not seconds, and is run in parallel (`--parallel`) in CI.
- Parallel runs have environment needs: on a cluster with the plain `C` locale the per-process
  databases do not fold Cyrillic, and many processes need `max_locks_per_transaction` raised
  (`compose.yaml` sets 256).
- `tests/` is formatted by Pint and rewritten by Rector, but not analysed by Larastan.
- Pest's `arch()` sees namespaced classes only; `env()` outside `config/` is caught in the routes,
  `bootstrap/app.php` and the migrations by a separate token-based test in the same file.
- The coverage floor of 40% is a guard against a collapse, not a measured target: coverage is not
  measured on the development machine, only in CI with pcov.
- Only part of the JavaScript has tests. Vitest (`tests/js`, `npm test`, in happy-dom) covers the
  shared modules of `resources/js/lib` and the two booking grids. The other page scripts are
  checked by parsing the inline scripts of every rendered page in node, by the build and manifest
  checks, and by rendering every page; the flows in a real browser are checked by hand.
- The Rector skip list is configuration to maintain: a new rule set can bring rules that need the
  same review.

## Alternatives considered

- **SQLite in memory for speed.** It would not run the exclusion constraint, the partial index, the
  `CHECK` constraints, `ILIKE`, the window functions or the row locks, so the most important
  behaviour would be untested.
- **A Larastan baseline at a higher level, such as 9.** Level 9 raised several hundred errors
  around `mixed` values from configuration and request arrays. Level 8 was reached with every error
  fixed in code; a baseline would have recorded problems instead of removing them.
- **PHPUnit classes instead of Pest.** Pest runs on PHPUnit and offers the same assertions; its
  `arch()` presets and datasets made the architecture and route-table rules short to state.
- **PHP-CS-Fixer instead of Pint.** Pint is a preset layer over PHP-CS-Fixer that follows the
  Laravel style out of the box, which the rest of the ecosystem uses.
