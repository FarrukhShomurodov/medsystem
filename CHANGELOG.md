# Changelog

All notable changes to MedSystem are recorded in this file. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versions follow
[Semantic Versioning](https://semver.org/spec/v2.0.0.html). The major version changes when a
route, a permission, the JSON of an `/api` endpoint or a deployment step changes in a way that
breaks existing installations or clients.

## [Unreleased]

## [2.0.0] - 2026-11-18

The portfolio refactor. The original application was upgraded to Laravel 13, its security holes
were closed, and its workflows were rebuilt as tested domain services. The commercial admin
template was replaced by an open-source front end built from npm.

| | 1.x | 2.0.0 |
|---|---|---|
| Framework | Laravel 11, spatie/laravel-permission 6, Laravel Excel 3.1 | Laravel 13, spatie/laravel-permission 8, Laravel Excel 4 |
| Automated tests | None: `tests/` held only the skeleton's `TestCase.php` | 1337 Pest tests in 89 files, in Unit, Feature and Arch suites, and 47 Vitest tests of the page scripts |
| Static analysis | PHPStan 1 without Larastan: a `phpstan.neon` with filesystem-root paths, options PHPStan 2 removed and 5 ad-hoc `ignoreErrors` | Larastan 3 at level 8 on `app/`, `bootstrap/app.php`, `database/` and `routes/`: 0 errors, no baseline, no `ignoreErrors`, no `@phpstan-ignore` |
| AJAX endpoints | 25 routes under `/api` with no session and no authentication | 24 routes under `/api`, each on the admin session and behind a permission |
| Front end | The commercial Frest HTML admin template, committed to `public/`: 911 files, 20.8 MB, plus scripts from CDNs | Tabler 1.7.5 (MIT) and every other library from npm, built by Vite into `public/build` (not committed), nothing from a CDN |
| CI and environment | None: `laravel/sail` was installed, but no compose file was committed | GitHub Actions; Docker Compose stack with PostgreSQL 18 |

When Larastan was first run on the upgraded code base, level 5 reported about 290 errors.

### Security

- **AJAX endpoints required no sign-in.** In 1.x the 25 routes under `/api` ran in Laravel's
  stateless `api` group, without a session, authentication or CSRF protection. Anyone could list
  every patient with every column, book appointments, change transfer statuses, cancel
  prescriptions and delete stored files. The one route that checked a permission refused everyone,
  staff included. The endpoints now live in `routes/ajax.php`, mounted under `/api` in the `web`
  group behind `auth:admin`. Each one requires a permission, writes are protected against
  cross-site request forgery, and guests get a JSON 401.
- **Dashboard routes without a permission.** 64 dashboard routes checked no permission. Every
  dashboard route now requires the admin session and a seeded permission, and
  `RouteAuthorizationTest` fails for a route that has none. Each of the 178 seeded permissions is
  checked by a route, a view or the code.
- **Writes on GET.** Changing a patient service's status, discarding main-warehouse stock and
  deleting a medical document response were GET requests. They are now PATCH or DELETE.
- **Files.** File deletion trusted a folder and a file name taken from the URL. Patient uploads
  were not validated and were stored on the public disk. Uploads are now limited by type, size and
  count and stored on the private disk. `PatientFileController` serves a file only to staff who may
  see the patient, and a file type a browser could run is downloaded rather than rendered. A file
  is deleted by its id through its patient.
- **Sign-in and sign-out.** Sign-in is throttled to five attempts a minute, and it issues a new
  session id and CSRF token. Sign-out used the default guard and left the admin session alive. It
  now signs out the `admin` guard, invalidates the session and accepts POST only.
- **Dismissed employees could still sign in.** They are now refused at sign-in, and an existing
  session or "remember me" cookie ends on the next request (`EnsureAccountIsActive`).
- **Password changes kept old sessions.** A new password now ends every other session of the
  account on its next request, on the pages and on `/api` (`AuthenticateSession`). The account
  that changes its own password stays signed in.
- **Main administrator accounts.** Holders of `edit_admins` or `edit_staffs` could change a
  `main_admin`'s e-mail or password. Now only a `main_admin` may edit or delete a `main_admin`
  (`AdminPolicy`). The last active `main_admin` can be neither deleted nor dismissed.
- **Role takeover through staff positions.** Saving a position named after an existing role,
  `main_admin` included, rewrote that role's permissions. Positions now link to their role by id
  (`staff_positions.role_id`), and reserved and taken role names are refused.
- **Favourite diagnoses** were toggled for an `admin_id` taken from the request. They now belong
  to the signed-in account.
- **Ids that became records.** Every select2 accepted free text, and several services created a
  record from any non-numeric value sent in place of an id. Free text is now opt-in
  (`.select2-tags`, used only on the address fields). Requests validate ids with `exists`.
- **Cross-site scripting in page scripts.** API data was interpolated into HTML strings. Server
  data now reaches scripts through JSON blocks and `data-*` attributes, and script-built HTML
  escapes every value. No view uses `{!! !!}`; `AllPagesTest` enforces this.
- **Closed stays.** A blank `map` field skipped the closed-stay check, so a discharged stay could
  be edited or deleted. `ExaminationMapIsActive` now reads the bound route parameters.
- **Appointment details.** `GET /api/receptions/{reception}` sent the whole appointment with its
  patient, stay, staff record and signature image. It now sends only what the calendar's dialog
  prints.
- **A doctor's account worked on every employee's calendar.** Booking, the doctor's calendar
  page, the free and booked slots, and showing or cancelling an appointment accepted any employee
  for a doctor's account. `ReceptionPolicy` now keeps a
  doctor's account on the doctor's own calendar, whatever its roles, and refuses anything else
  with a 403. An employee id that names nobody is refused like a colleague's, and the doctors of
  a service are the doctor alone. Regression tests in `ReceptionBookingTest`,
  `StaffAvailabilityTest`, `ReceptionPagesTest` and `ReceptionStatusTest`; `ApiAccessTest` fails
  for an `/api` route with a `{staff}` or `{reception}` parameter that does not ask the policy.
- **Deleting a medical document.** Any filled-in document could be deleted by its id. Only an
  initial nursing assessment that no stay holds yet can be deleted now (`DocumentResponsePolicy`).
- **Reading a medical document by id.** `GET /dashboard/document/response/answers/{id}`
  (`documents.show.answer`) showed the answers of any patient's nursing and scored sheets to any
  signed-in account, though only the stay forms link it. It now needs `create_patient_map` or
  `edit_maps` and opens only an initial nursing assessment, to the permission of the form that
  links it: one no stay holds yet for `create_patient_map`, one of an open stay for `edit_maps`.
  Every other sheet, and a closed stay's assessment, is a 403 that says why
  (`DocumentResponsePolicy`); every sheet stays readable on the patient card. Regression tests in
  `MedicalDocumentsTest` ("opening a filled-in document").
- **The nurse window's recommended actions reached every sheet.** Even behind the nurse window's
  permission, `GET /api/document-scores/{document}/{response}` and
  `POST /api/document/finish/action` read and marked done the actions of any patient's scored
  sheet, in another department or on a closed stay. They now accept only a sheet of an open stay
  the account's nurse window shows (`DocumentResponsePolicy`). Regression tests in
  `NurseWindowTest` ("the recommended actions of a scored sheet").
- **Formulas in the Excel exports.** A text value starting with `=` (a name, a comment) was written
  to the cashier and registrar exports as a formula, which the spreadsheet then ran. Such values
  are now written as text (`TextSafeValueBinder`).
- **Malformed input.** A query or body that was not valid UTF-8 or carried a NUL byte caused a
  database error (500). It is now refused with 400 (`RejectMalformedInput`).
- **Browser hardening headers** on every page and AJAX response (`SecurityHeaders`):
  `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, and
  `Strict-Transport-Security` over HTTPS.
- **Telescope** was registered on every boot, although it is a dev dependency. It is now
  registered only when `APP_ENV=local` and is not auto-discovered.
- **Initial administrator.** `AdminSeeder` takes its credentials from `ADMIN_EMAIL` and
  `ADMIN_PASSWORD` and refuses the default password in production. The `password` attribute of
  `Admin` is cast to `hashed`, and the remember token is hidden.

### Fixed

Every fix has a regression test.

- **Stock**
  - Approving a supply request committed part of the stock moves when one item failed. It could
    approve a request twice, and it overwrote every batch of a medication with one quantity.
    Approval now runs in one transaction, decides a request once (approved or rejected), and moves
    exactly the requested quantity, batch by batch.
  - Completing a transfer saved the status before the stock check and outside the transaction.
    Completing it again took the stock out a second time. A transfer now moves stock once, and
    only from `pending`.
  - Dispensing a prescription never took the drug out of any warehouse. Stock now leaves the stay
    department's medication warehouse, or the main warehouse, when the registrar confirms the
    order. A returned drug goes back into the batches it came from.
  - "Discard" in the main warehouse, a GET request, deleted every batch of the medication without a
    record. A write-off now deletes the one selected batch and records the movement.
  - Two batches of one drug in the same receipt were merged into one.
  - Changing the type of a warehouse that held stock stranded that stock under the wrong
    catalogue. A warehouse's type is now fixed once it holds or held stock, or has a request or
    transfer pending.
- **Billing**
  - "Pay selected" at the cash desk crashed or applied only part of the payment: procedures hit
    the price-list model, `paid` broke the status constraints, and nothing ran in a transaction.
    A payment now covers the orders of one patient, all or nothing, under row locks. A repeated
    payment request is refused.
  - The registrar's total and its export left out procedures.
  - The cashier's status filter was combined with a fixed status, so filtering by any other status
    returned nothing. The prescription date filter read a column that does not exist, and the name
    search was case-sensitive.
  - Registrar confirmation updated the orders of every stay of the patient, outside a
    transaction, on a route without a permission. It now confirms the orders of the active stay in
    one transaction and dispenses their drugs, or confirms nothing when a drug is short.
  - Unpaid orders were charged at the current price-list price and currency. Changing a price, or
    moving a service from UZS to USD, re-priced old orders. Orders now keep the price and currency
    they were placed at.
  - The service form stored the typed price less 12% VAT, and again at every edit that changed it,
    while the import stored the price as given; the cash desk charged whatever was stored. Every
    price is now stored and charged as typed, VAT included.
  - An order left the paid list, and the dashboard's paid figure, as soon as it moved on (carried
    out, the visit done, the drug given or returned). A payment now stamps `paid_at`, and an order
    counts as paid on the day it was paid.
  - A paid appointment could be cancelled, erasing a payment that had been taken. After payment
    only the visit (`done`) follows.
- **Clinical**
  - The pain scale and the outpatient fall-risk (КДО) sheet always stored a score of 0.
    `documents:rescore` recomputes the scores stored by 1.x.
  - A cancelled or returned prescription could be extended, because the guard condition was
    always true.
  - Prescribing dropped the compatible drugs of inpatient prescriptions and overwrote those of
    earlier ones.
  - The interaction warnings on the patient card matched cancelled prescriptions and compared drug
    names case-sensitively. They now cover the prescriptions in progress, in any letter case.
  - The service import failed on every valid row. The dangerous-combination import skipped the
    first data row and mapped the severity columns wrongly.
  - Ordering the same procedure on two dates merged the orders into one, and ordering a procedure
    by a typed name failed on the price constraint.
- **Patients**
  - Searching patients by id returned a 500 on PostgreSQL. Name filters matched only whole names,
    and the birth-date filter returned everyone born on or after the date.
  - A patient could have two open stays when the form was posted twice. A row lock and a partial
    unique index now allow one.
  - A department typed into the stay form created an unrelated institution row.
  - The nurse window crashed for a patient without an open stay and listed discharged patients.
  - Deleting a patient or a stay deleted, through the cascades, paid orders and dispensed
    prescriptions. Such deletions are now refused with a message.
  - Deleting a stay answered 500, because it redirected to a route that did not exist.
- **Scheduling**
  - The appointment list filtered by the staff member with id 1 whenever any doctor was chosen.
  - Cancelling an appointment from the calendar always answered 403.
  - A doctor could be double-booked: only identical start times were compared. Bookings are now
    checked on the server against the doctor's reception hours, appointment services, appointment
    length, absences and existing appointments. The `receptions_no_overlap` exclusion constraint
    backs this up in the database.
  - An appointment length of 0 or less sent the slot calculation into an endless loop.
  - Availability mixed UTC with the hospital's time zone. Between midnight and 05:00 it showed
    the slots of the wrong day.
  - Renaming a staff position created a new role, while its staff kept the old one.
  - A service withdrawn from the price list could still be booked.
  - Sick leave or a change of reception hours hid the appointments already booked in that time.
    They stay in the calendars now, and saving the change lists the appointments it leaves
    without cover.
  - A booking could start between two slots of the doctor's grid (10:05 for a 20-minute doctor),
    leaving gaps no calendar could offer. It is now refused with a message; appointments booked
    on an older grid are kept and still block the slots they overlap. Regression tests in
    `ReceptionBookingTest` and `SlotGridTest`.
  - The doctor's calendar page opened by URL for a service the employee does not offer, and then
    every slot it offered was refused. It now goes back to the booking page with the reason.
    Regression test in `ReceptionPagesTest`.
  - An employee registered with a dismissal date stayed employed.

### Changed

- **Upgrades:** Laravel 11 to 13, spatie/laravel-permission 6 to 8, Laravel Excel 3.1 to 4,
  Laravel Tinker 2 to 3. The tests are written for Pest 4 instead of plain PHPUnit. CI runs them on
  PHP 8.3 and 8.4 against PostgreSQL 18.
- **Application structure:** workflow logic moved out of the controllers into module services
  (`Billing`, `Clinical`, `Patients`, `Scheduling`, `Stock`), and page read models into
  `app/Queries`. Validation lives in form requests, most of which hand the services a typed
  payload. Permissions are declared through `HasMiddleware` in the controllers or on the routes,
  and the decisions that depend on the record live in three policies in `app/Policies`.
  The rules are in
  [docs/architecture/folder-layout.md](docs/architecture/folder-layout.md).
- **Statuses and vocabularies:** 19 string-backed enums in `app/Enums`, each with a Russian
  label, cast on the models and validated with `Rule::enum()`. The five lifecycles declare their
  allowed transitions once (`HasLifecycle`). Russian values stored in `equipment.status` and in
  the packaging types are migrated to ASCII values, and the Russian words became the labels.
  Polymorphic columns store morph-map aliases instead of class names.
- **Stock:** one writer, `StockLedger`, changes every quantity. It locks the rows and takes
  unexpired batches, earliest expiry first, and records each change in the append-only
  `stock_movements` ledger. A shortage rolls back the whole document, and a warehouse or pharmacy
  holding stock cannot be deleted.
- **Money:** money columns are `decimal(14, 2)`, and the per-unit purchase price is
  `decimal(14, 4)`. Laravel's default `decimal(8, 2)` capped amounts at 999 999.99, too little for
  prices in UZS. Amounts are calculated in integer minor units and totalled per currency.
- **Database integrity:** reference foreign keys use `ON DELETE RESTRICT` or `SET NULL`. Deleting
  a record that is still in use shows a Russian message instead of an error page. Indexes were
  added on foreign keys and filter columns, and unique keys prevent duplicates from double clicks.
  All 27 new migrations have a working `down()`, and CI rolls the whole chain back over the demo
  data. Rolling back the money widening keeps the wider columns: narrowing them again would
  overflow on UZS amounts.
- **Search:** the patient list, the cashier list and the patient picker match part of a name,
  regardless of letter case, and treat `%`, `_` and `\` literally. In the picker every typed word
  must match.
- **API contracts (breaking for any other client):**
  - `GET /api/patients` searches on the server: it takes `term` or `q` (at most 100 characters)
    and returns at most 20 items of `{id, full_name, birth_date}`.
  - `GET /api/staff-by-service/{service}` returns `{id, full_name, admission_period}`.
  - `GET /api/receptions/{reception}` returns only `id`, `date`, `start_time`, `end_time`,
    `comment`, `status`, `status_label`, `can_cancel`, `patient_id`, `patient_name`, `staff_name`
    and `position`.
  - `GET /api/diagnoses` is new: the patient card's diagnosis picker searches the ICD-10 catalogue
    on the server (at most 50 rows) instead of embedding it in the page.
  - `GET /api/examination-map-by-patient/{patient}`: `patient_name` is the patient's full name, and
    `institution_name` is now `department_name`.
  - Every page and endpoint answers 404 to a record id in the URL that is not a whole number or
    lies beyond `bigint` (`/dashboard/patients/abc`), instead of a 500. The booking endpoints
    (`staff-by-service`, `staff-availability-today`, `staff-booked-list-today`,
    `staff-availability-week`) and the doctor's calendar page also constrain their ids in the route.
- **Names and labels:** patients and staff are printed as "Фамилия Имя Отчество" everywhere. The
  transfer statuses read "Завершён" and "Отменён", and gender reads "Мужской" and "Женский" on
  every screen. An order's status agrees with its noun: a service or procedure is "Подтверждена"
  or "Оплачена", an appointment "Новый" or "Отменён", a prescription "Выполнен", "Возвращён" or
  "Отменён". The staff position form names the office, floor and main-warehouse permissions in
  correct Russian ("Создание кабинетов", "Просмотр остатков общего склада").
- **Seeders** are idempotent. `PermissionSeeder` and `DocumentSeeder` insert in bulk. Demo data is
  seeded only when `APP_ENV=local`.
- **Configuration:** `.env.example` targets PostgreSQL with the defaults of `compose.yaml`, the
  `Asia/Tashkent` time zone and the Russian locale. `pint.json` used the misspelt key `present`;
  it now uses the `laravel` preset with the project's rules, and Pint and Rector were applied to
  the whole tree.
- **User interface:** every page moved to Tabler, with one shared set of conventions
  ([docs/architecture/frontend.md](docs/architecture/frontend.md)). Long inline scripts became
  page modules in `resources/js/pages`. moment.js was replaced by native `Date` and `Intl`.
  Pagination is Bootstrap 5, in Russian. Sidebar sections and labels were reorganised, and typos
  in them fixed.

### Removed

- The Frest HTML admin template: 911 files, 20.8 MB, from `public/css`, `public/js`, `public/img`
  and `public/vendor/{css,fonts,js,libs}`, including the commercial FormValidation library. Also
  every asset the views loaded from a CDN.
- The Laravel Mix and Tailwind build files (`webpack.mix.js`, `tailwind.config.js`,
  `postcss.config.js`). `package.json` now holds the Vite build.
- The Analyze and Laboratory module, whose routes were commented out: its controllers, requests,
  services, models, views and permissions.
- The unused `web` guard, the `User` model and its empty route file, together with other code
  nothing called: the `Image`, `WarehouseInventory` and `WarehouseTransfers` models,
  `HandlesStaffAvailability`, `MedicalEquipmentService`, `draf.php`, `config/responsecache.php`,
  and the framework caches that were committed under `bootstrap/cache`.
- The tables behind the removed modules and models, which no code read: `analyzes` and the
  `analyze_*` tables, `laboratories`, `users`, `images`, `warehouse_inventories` and
  `warehouse_transfers`.
- `laravel/sail` and the stale `stock` column of the main-warehouse batches. The batches' own
  quantities are the stock.
- The `SetLocale` middleware and the Uzbek validation messages (`resources/lang/uz`): nothing let a
  user choose a language, so the dashboard was always Russian.

### Added

- **Home dashboard:** a role-aware summary of the hospital's day replaces the welcome placeholder.
  It shows patients in hospital, the day's appointments by status, orders waiting for the cashier
  and those paid today, the registrar's queue, the latest admissions, and stock alerts (supply
  requests to decide, batches near or past expiry, items at zero). Each of its 11 cards is shown
  only to accounts that may open the page it links to, and it reads its figure through that
  page's own query.
- **Supply request rejection,** with an optional reason, next to approval.
- **Commands:** `documents:rescore` lists the assessment sheets whose stored score differs from
  their answers and saves the corrected scores with `--force`. `permissions:prune` lists the
  permissions the seeder no longer defines and deletes them with `--force`.
  `patient-files:privatize` lists the patient files 1.x left on the public disk and moves them to
  the private disk with `--force`.
- **Front end:** a Tabler layout with a sidebar defined once
  (`App\Support\Navigation\Navigation`), each link carrying the permission of its route. Also a
  Russian sign-in page, and Russian error pages (403, 404, 419, 429, 500, 503) that render
  without a database or a front-end build. Shared components: page header, flash messages,
  confirmation modal, delete button, empty state, and status badge and icon. A modal asks to
  reload the page when the session expires. Inter and Boxicons are self-hosted, and jQuery 4,
  select2, flatpickr, bs-stepper and ApexCharts are pinned npm packages.
- **Tests:** 1337 Pest tests and 47 Vitest tests. Every regression test of the fixes above, plus:
  - the session and permission rules of every route, and the CSRF protection of the AJAX writes;
  - every GET page under `/dashboard` rendered over the demo data for the main administrator,
    the registrar and the cashier, with markup and accessibility checks and a Node.js parse of
    every inline script;
  - fixed query counts that catch N+1 queries on the patient list and card, the nurse window, the
    cashier list, the staff and admin lists and the dashboard;
  - migrations, rollbacks and seeders on an empty database;
  - 24 architecture rules;
  - the page scripts' shared modules and both booking grids, run in happy-dom by Vitest.
- **Factories and demo data:** 51 model factories with states. A fictional demo hospital for
  local installations, with the same structure on every seed (five departments, 40 patients, twelve
  stays) and names drawn anew by Faker.
- **Tooling:** Larastan 3, Rector 2 with rector-laravel, Pint, and the Composer scripts `setup`,
  `lint`, `lint:fix`, `analyse`, `refactor`, `refactor:fix`, `test`, `test:coverage` and `ci`.
- **Docker:** a multi-stage `docker/php/Dockerfile` (`dev` with pcov, `assets` with the Vite build,
  `prod`) and `compose.yaml`. The stack runs the app, nginx, PostgreSQL 18 with a separate test
  database, and an optional Vite dev server, with every port on localhost only. A `Makefile`
  drives it.
- **CI:** a GitHub Actions workflow with these jobs:
  - the page script tests (Vitest) and the front-end build with a manifest check;
  - dependency audit;
  - Pint;
  - Larastan and Rector;
  - the test suite on PHP 8.3 and 8.4 against PostgreSQL 18, after migrating and seeding an empty
    database and rolling every migration back and forward again, with a coverage minimum of 40%.
- **Repository:** Dependabot, pull request and issue templates, [LICENSE](LICENSE) (MIT),
  [SECURITY.md](SECURITY.md), [CONTRIBUTING.md](CONTRIBUTING.md) and this changelog.
- **Documentation:** the README, the architecture notes in `docs/architecture/` and the
  screenshots in `docs/screenshots/`.

### Upgrading a 1.x installation

1. Back up the database, then run `php artisan migrate`. The migrations rewrite stored class
   names to morph aliases, so run them together with the new code: until they have run, accounts
   have no roles. They also backfill the price and currency of existing orders from the current
   price lists, and convert the Russian equipment and packaging values (PostgreSQL only). The
   migration that drops the tables of the removed modules stops while any of them holds rows and
   names those tables: export them (`pg_dump --data-only --table=...`), empty them, and migrate
   again.
2. Run `php artisan documents:rescore` to review the assessment scores 1.x stored, then
   `php artisan documents:rescore --force` to correct them.
3. Run `php artisan permissions:prune`, then `php artisan permissions:prune --force` to delete the
   permissions 2.0.0 no longer defines, such as those of the removed laboratory module.
4. Run `php artisan patient-files:privatize`, then `php artisan patient-files:privatize --force` to
   move the patient documents 1.x stored on the public disk (`storage/app/public/patient_files`),
   which the web server served without a sign-in, to the private disk. 2.0.0 reads patient files
   from the private disk only. The shipped `public/.htaccess` refuses `/storage/patient_files/`
   meanwhile; under nginx, add `location ^~ /storage/patient_files/ { deny all; }` as in
   `docker/nginx/default.conf`.
5. Set `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `APP_TIMEZONE=Asia/Tashkent` and `APP_LOCALE=ru`, then
   build the front end with `npm ci && npm run build`. `public/build` is no longer committed.

## [1.x]

The original application, up to commit `7f6b798` (2025-10-26).
