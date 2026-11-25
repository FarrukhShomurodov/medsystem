# 1. PostgreSQL as the only supported database

- Status: Accepted
- Date: 2025-11

## Context

Several rules of the hospital are about what may exist in the database at the same time, not about
a single row:

- an employee is never booked for two appointments that overlap in time;
- a patient has at most one open hospital stay (examination map);
- a stock quantity never goes below zero, and an ordered quantity is always positive;
- a status column holds only the values of its lifecycle.

The original code checked none of these under concurrency, and some not at all: appointments
matched on exact start time only (double booking), a double-submitted admission form opened a second
stay, and stock was decremented on the wrong rows. The application already ran on PostgreSQL in
practice (Laravel's `enum()` columns were `varchar` with a `CHECK`, and an `ILIKE` on a bigint
column crashed the patient search), while `.env.example` still named SQLite and the tests did not
run against the real engine.

Patient and staff names are Cyrillic, and every search form must match them regardless of case.

## Decision

PostgreSQL is the only supported database. CI and the compose stack run `postgres:18-alpine`; the
test suite runs on PostgreSQL (`phpunit.xml` defaults `DB_CONNECTION` to `pgsql`). There is no
portability layer, and the schema and the queries use PostgreSQL features wherever they state a rule
more directly than application code. Each of these constraints backs a check the application
already makes; none replaces it.

| Feature | Where | What it guarantees |
|---|---|---|
| Exclusion constraint `receptions_no_overlap`: GiST over `int8range(staff_id, staff_id, '[]')` and `tsrange(date + start_time, date + greatest(start_time, end_time))`, only for rows `WHERE status <> 'cancelled'` | migration `2025_11_19_130100_add_overlap_constraint_to_receptions_table` | no two live appointments of one employee overlap. `ReceptionService::book()` turns SQLSTATE `23P01` into `ReceptionException::slotTaken()` |
| `CHECK (end_time > start_time) NOT VALID` (`receptions_end_after_start`) | same migration | an appointment ends after it starts |
| Partial unique index `examination_maps_one_active_per_patient` on `(patient_id) WHERE is_active` | `2025_11_19_120000_add_one_active_examination_map_per_patient_index` | one open stay per patient |
| `CHECK (quantity >= 0)` on the seven stock tables, `quantity > 0` on transfer lines and supply request items, all `NOT VALID` | `2025_11_19_100200_add_quantity_checks_to_stock_tables` | stock never goes negative ([ADR 5](0005-append-only-stock-ledger.md)) |
| `varchar` + `CHECK` for every enum column, replaced by a migration when a lifecycle changes | e.g. `2025_11_18_100200_add_paid_status_to_patient_services_and_procedures`, `2025_11_20_110200_store_english_values_of_equipment_status_and_packaging_type` | a column admits exactly the values of its PHP enum ([ADR 3](0003-backed-enums-and-morph-aliases.md)); `tests/Feature/Database/EnumColumnsTest.php` reads each `CHECK` through `pg_get_constraintdef()` and compares it with `Enum::values()` |
| `whereLike()`, which compiles to `ILIKE` | `App\Queries\LikePattern::contains()`, used by `PatientListQuery`, `PatientPickerQuery` and `CashierItemsQuery` | case-insensitive search; `%`, `_` and `\` typed by the user are matched literally |
| Window functions, `UNION ALL` over the four order tables, `::text` casts, `NULLS LAST`, `date + time` arithmetic | `RegistrarBasketQuery::queue()`, `CashierItemsQuery` | one query per list, total or queue, sorted and paginated by the database |
| `DISTINCT ON`, `UPDATE ... FROM`, `setval(pg_get_serial_sequence(...))` | the migrations that remove duplicates before adding a unique index, the price and currency backfills, `SeedsRowsWithFixedIds::syncIdSequence()` | data migrations in one statement each |
| SQLSTATE `23503` / `23001` on a `DELETE` request | `bootstrap/app.php` | a restricted delete is answered with a Russian message, not a 500 ([ADR 9](0009-restrict-deletes-and-retain-records.md)) |

The concurrency rules of [ADR 4](0004-row-locks-in-a-fixed-order.md) also rely on PostgreSQL
semantics: under READ COMMITTED an `UPDATE` that matches only orders still in their ordered status
re-evaluates that condition on a row another transaction has just changed
(`RegistrarConfirmationService`), and a detected
deadlock aborts one transaction, which `DB::transaction(..., 3)` runs again.

## Consequences

### Positive

- The invariants hold for every writer, including one that bypasses the services: tinker, a future
  import, a bug. The tests prove the backstops directly, for example `ReceptionBookingTest` "lets the
  database refuse an overlapping appointment written past the service" and `ExaminationMapTest`
  "keeps two open stays of one patient out of the database".
- The tests exercise the same engine as production: `CHECK` constraints, row locks, error codes and
  the transaction abort after a failed statement behave in tests as they do in the hospital.
- The read models can be written for the query planner (one aggregate query for the cashier totals,
  one window query for the registrar queue) instead of for the lowest common denominator.
- The booking constraint needs no extension: comparing the staff id as a one-value range avoids
  `btree_gist`.

### Negative

- The application cannot move to MySQL or SQLite without rewriting migrations, queries and tests.
  There is no in-memory SQLite test run; every developer needs a PostgreSQL server (the compose
  stack provides one, with a separate `medsystem_testing` database created by
  `docker/postgres/init`).
- Case-insensitive Cyrillic search depends on the database locale. `ILIKE` folds Cyrillic only when
  the database has a UTF-8 aware locale, which is the default of the official image (`en_US.utf8`);
  the builtin `C.UTF-8` provider also works. A database created with the plain `C` locale matches
  Cyrillic case-sensitively. This bites local parallel test runs on such a cluster: Laravel creates
  the per-process test databases from `template1`, and the Cyrillic search tests then fail there
  only. CI is unaffected.
- `pest --parallel` with many processes rebuilds one schema per process at once and can run out of
  PostgreSQL's lock table; `compose.yaml` starts the server with `max_locks_per_transaction=256`.
- `NOT VALID` constraints check new and changed rows only, not rows that were already wrong. An
  exclusion constraint cannot be added `NOT VALID` at all, so the migration leaves the double
  bookings that existed before it out of the constraint by id. Those ids stay in the constraint
  definition until it is recreated.
- A few migrations return early when the driver is not `pgsql`, while others use PostgreSQL syntax
  unguarded. The early returns are not a portability path: migrating on another driver fails.

## Alternatives considered

- **A portable schema with every rule in PHP.** Rejected: a check in PHP alone has a race window
  between the read and the write, and a write that bypasses the service breaks the rule silently.
- **MySQL.** It has no exclusion constraints and no partial indexes, so the booking and open-stay
  rules would need extra tables or triggers.
- **A table of bookable slots with a unique key.** It turns the overlap rule into a unique index, but
  the slots would have to be generated from every schedule and regenerated on each schedule change.
  The exclusion constraint states the rule over the appointments themselves.
- **`citext` columns or a `COLLATE` clause for search.** One intermediate version used
  `COLLATE pg_c_utf8` (PostgreSQL 17 and later) on the patient list while the cashier used `ILIKE`.
  The code now has one way to match text, `whereLike()` with `LikePattern`, which works on the
  default image without column changes.
