# Contributing

Issues and pull requests are welcome. Report a vulnerability privately as described in
[SECURITY.md](SECURITY.md), never in a public issue.

The user interface is in Russian. Code, comments, test names, commit messages and documentation are
in English.

## Setup

Follow [README.md](README.md) to install the application, either with Docker (`make up`, then
`make install`) or on your own PHP and PostgreSQL. The versions the project is checked against:

- PHP 8.3 (CI also runs the tests on 8.4), with the `bcmath`, `gd`, `intl`, `pdo_pgsql` and `zip`
  extensions
- PostgreSQL 18, as in `compose.yaml` and CI
- Node.js 22.12 or later (`engines` in `package.json`); CI uses Node.js 24

Before you change a part of the code, read where its classes belong in
[docs/architecture/folder-layout.md](docs/architecture/folder-layout.md). For views and scripts,
read [docs/architecture/frontend.md](docs/architecture/frontend.md).

## The test database

> [!WARNING]
> The Feature tests use `RefreshDatabase` (`tests/Pest.php`). The first test of a run executes
> `migrate:fresh` on the database the tests connect to: every table in it is dropped and created
> again. Run the tests only against a database you can lose.

- `phpunit.xml` sets `DB_DATABASE=medsystem_testing` as a default, not a forced value. A
  `DB_DATABASE` variable in the environment of the test process wins. CI sets its own, and so does
  the compose stack.
- **Docker:** the `app` container's environment points at the development database, so
  `docker compose exec app composer test` would wipe your development data. Use `make test`,
  `make coverage` or `make ci`: they pass `DB_DATABASE=medsystem_testing` to the container. The
  `db` service creates that database (`docker/postgres/init`) the first time its volume is
  initialised.
- **Without Docker:** create `medsystem_testing` yourself, with UTF-8 encoding and a UTF-8 locale.
  In a database with the plain `C` locale, `ILIKE` does not fold the case of Cyrillic letters, and
  the search tests fail. In the shell that runs the tests, never export a `DB_DATABASE` that names
  a database you want to keep.
- **Parallel runs** (`composer test -- --parallel`) give every worker its own database,
  `<DB_DATABASE>_test_<N>`, created from `template1`. That template needs a UTF-8 locale too. Many
  workers rebuilding their schema at the same moment need `max_locks_per_transaction` of 256 or
  more on the server; `compose.yaml` sets it. On another server, limit the workers with
  `--processes=4`.
- The tests pin `APP_TIMEZONE=Asia/Tashkent`, whatever your `.env` says: the hospital's calendar
  is part of the domain.

## Branches and commits

- `main` is the default branch. Branch from it and open the pull request against it.
- Name a branch `<type>/<short-description>` in kebab case, with the types listed below, for example
  `fix/transfer-completed-twice` or `feat/cashier-waiting-list`.
- Commits follow [Conventional Commits 1.0](https://www.conventionalcommits.org/en/v1.0.0/):

  ```
  <type>(<scope>): <summary>
  ```

  - **Types:** `feat`, `fix`, `refactor`, `perf`, `test`, `docs`, `style` (formatting only), `build`
    (Composer, npm, Vite, Docker), `ci`, `chore`.
  - **Scopes:** the modules, `billing`, `clinical`, `patients`, `scheduling` and `stock`, or a
    cross-cutting area: `auth`, `dashboard`, `frontend`, `db`, `docker`, `deps`. Leave the scope out
    when a change spans several.
  - **Summary:** imperative mood, lower case, no full stop, at most 72 characters. The body explains
    why the change is needed.
  - **Breaking changes:** add `!` after the type or scope and a `BREAKING CHANGE:` footer. Use them
    when the JSON of an `/api` endpoint changes shape, a route or a permission is renamed or
    removed, or a deployment needs a manual step.
  - Example: `fix(stock): move transfer stock only once when completed twice`.
- Keep one logical change per pull request. Pull requests are squash-merged, so the pull request
  title becomes the commit on `main` and follows the same format.
- Dependabot's pull requests use the prefixes `chore(deps)`, `chore(deps-dev)` and `ci(deps)`
  (`.github/dependabot.yml`).

## Before you open a pull request

Run the checks CI runs. Every one must pass.

| Check | Local | Docker | CI job |
|---|---|---|---|
| Code style (Pint) | `composer lint` (fix with `composer lint:fix`) | `make lint` (fix with `make fix`) | Code style (Pint) |
| Static analysis (Larastan, level 8 from `phpstan.neon.dist`) | `composer analyse` | `make analyse` | Static analysis (Larastan) |
| Rector has nothing left to change | `composer refactor` (apply with `composer refactor:fix`) | `make refactor` | Static analysis (Larastan) |
| Tests (Pest: Unit, Feature, Arch) | `composer test` | `make test` | Tests |
| All four above | `composer ci` | `make ci` | |
| Front-end tests and build | `npm ci`, `npm test` (Vitest), `npm run build`, then `node tests/Feature/Frontend/check-manifest.mjs` | `make assets` (the build) | Front-end tests and build (Vitest, Vite) |
| Dependency audit | `composer audit --abandoned=report` and `npm audit --omit=dev --audit-level=high` | | Dependency audit |

The CI tests job also runs `php artisan migrate:fresh --seed --force` on an empty database,
`php artisan migrate:refresh --force` (every `down()` over the demo data), and then
`vendor/bin/pest --parallel --coverage --min=40`.

What the suite and the tools enforce, so that a pull request does not fail on them late:

- **No suppressed findings.** There is no Larastan baseline, no `ignoreErrors` and no
  `@phpstan-ignore`. Fix the type instead.
- **Permissions on every route.** `RouteAuthorizationTest` fails for a dashboard or `/api` route
  without the admin session and a seeded permission. A new permission goes into
  `PermissionCatalog::PERMISSIONS` with its Russian label, and something must check it.
- **Every page renders.** `AllPagesTest` opens every GET page under `/dashboard` over the demo
  data and checks its markup: labelled fields, named buttons and links, unique ids, CSRF tokens in
  POST forms, no inline event handlers, no `{!! !!}` in any view. `NavigationTest` requires exactly
  one highlighted sidebar link per page; add a new page's route name to the `active` patterns of its
  link in `App\Support\Navigation\Navigation`.
- **Inline scripts parse.** The page tests parse every inline script with Node.js. Without `node`
  on the `PATH` those tests are marked incomplete or skipped instead of passing, so install
  Node.js locally.
- **Architecture rules.** `tests/Arch/ArchitectureTest.php` holds them: for example, services do
  not depend on the HTTP layer, `env()` is read only in `config/`, and enums in `app/Enums` are
  string-backed with a `label()`.
- **Migrations.** Add a new migration rather than editing one that has been merged. Write it for
  PostgreSQL and give it a working `down()`. Seeders must stay safe to run again (`SeedersTest`).
- **Bug fixes** come with a regression test that fails without the fix.

## Pull requests

- Fill in the pull request template.
- Add a line under `[Unreleased]` in [CHANGELOG.md](CHANGELOG.md) for anything a user, an
  operator or a client of the `/api` endpoints would notice.
- For a visible change, attach a screenshot of the page.
- Never commit real patient data, personal e-mail addresses or phone numbers: not in code,
  factories, seeders, tests or screenshots. Demo and test data are fictional: the seeders and tests
  use `.test` addresses, and the factories generate theirs with Faker.
