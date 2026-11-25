# 12. Reference data with fixed ids named in code, idempotent seeders, demo data through the domain services

- Status: Accepted
- Date: 2025-11

## Context

Some reference rows carry behaviour, not only a name: the doctor position is the one patients book
appointments with, document 1 is the initial nursing assessment, documents 2 to 8 are the scored
scales (Morse, Humpty Dumpty, FLACC ...), the main warehouse is the one receipts go into, the three
warehouse types decide what a department warehouse may store. The original code found them by
magic numbers (`main_warehouse_id = 1` in several services) or by Russian names (`'Регистратор'`,
`'Доктор'`, document titles), so renaming a row in the dashboard changed what the code did.

PostgreSQL sequences are not rolled back by the transactions `RefreshDatabase` wraps around each
test, so after the first test inserted a main warehouse, later tests got id 2, 3 and so on, and
every `id = 1` lookup broke.

The seeders also run on databases that are in use: a re-run must neither duplicate rows nor undo
what the hospital changed on its screens.

## Decision

1. **Each fixed id is a named constant of its model**, and code uses the constant:
   `StaffPosition::DOCTOR`, `HEAD_NURSE`, `NURSE`, `HEAD_OF_DEPARTMENT`, `REGISTRAR`, `CASHIER`
   (ids 1 to 6, listed in `StaffPosition::SEEDED_IDS`); `Document::INITIAL_NURSING_ASSESSMENT` (1)
   and the cases of `MedicalScale`, int-backed by the document ids 2 to 8; `MainWarehouse::MAIN` (1);
   `WarehouseType::MEDICATIONS`, `HOUSEHOLD_MATERIALS`, `MEDICAL_SUPPLIES` (1 to 3);
   `ServiceType::LAST_SYSTEM_TYPE_ID` (the seeded types 1 to 21 are system types). The days of the
   week are seeded as ids 1 to 7 with Monday = 1, the ISO weekday numbers the schedules are matched
   on.
2. **Seeders insert those rows with their ids** through
   `Database\Seeders\Concerns\SeedsRowsWithFixedIds`: `firstOrCreateWithId()` creates the row with
   the given id unless it exists, and leaves an existing row as it is; `syncIdSequence()` moves the
   table's sequence past the explicit ids (`setval(pg_get_serial_sequence(...))`), so later inserts
   do not collide. `DayOfWeekSeeder`, `DocumentSeeder`, `ServiceTypeSeeder`, `StaffPositionSeeder`
   and `WarehouseSeeder` use it.
3. **Seeded rows the code relies on cannot be deleted.** A seeded position can be renamed, but
   `StaffPositionController::destroy()` refuses to delete it (`StaffPosition::isSeeded()`);
   `ServiceTypeController` refuses to edit or delete a system type (`ServiceType::isSystem()`).
4. **`DatabaseSeeder` seeds reference data only, and every seeder in it is safe to run again**:
   permissions, positions with their roles, the first administrator, days of the week, service
   types, vital parameters, warehouses and the scored documents. `PermissionSeeder` inserts only the
   missing permissions and never takes a permission away from a staff role, so grants made on the
   position screen survive a re-run; a permission dropped from the list is removed only by
   `php artisan permissions:prune --force`. `AdminSeeder` never resets an existing administrator's
   password and refuses the default password in production. `SeedersTest` runs the reference
   seeders twice and expects the same rows.
5. **Demo data is local, fictional and goes through the domain where it matters.** `DemoSeeder`
   runs only when `APP_ENV=local` and skips itself when the database already has patients. It
   builds a small hospital (departments, staff with schedules, patients, a price list, stock and
   clinical activity) from factories, but uses the domain services wherever a workflow has side
   effects: prescriptions are dispensed through `PatientMedicationService::sendToFloor()`, so stock,
   ledger and statuses agree, and the Morse fall-risk sheet is saved through
   `DocumentResponseService`, so its stored score matches its answers. Every dashboard card has
   data to show ([dashboard.png](../screenshots/dashboard.png)).

## Consequences

### Positive

- The code reads as the hospital speaks (`StaffPosition::DOCTOR`, `MainWarehouse::MAIN`) and no
  longer depends on what a row is called on screen.
- Tests and fresh installs get the same ids as production, whatever the state of their sequences.
- Re-running `db:seed` after a deploy is safe and adds what is missing, such as new permissions.
- A demo database looks like a working day of the hospital, which is also what the page sweep of
  [ADR 10](0010-tabler-and-vite-instead-of-a-commercial-template.md) renders.

### Negative

- The ids are a contract with every database. A database whose position 1 is not the doctor, for
  example one restored from a dump made before the seeders existed, behaves wrongly without any
  error.
- Seeded positions can be renamed, so the name on screen can drift from the meaning of the
  constant: renaming "Доктор" to "Врач" is the intended use, but nothing stops position 1 from being
  renamed to something that is not a doctor.
- `syncIdSequence()` is PostgreSQL-specific and must be called after every explicit insert; a new
  fixed-id seeder that forgets it leaves the sequence behind the rows.
- The demo stock is created by factories, not received through the application, so the stock
  ledger holds no receipt movements for it ([ADR 5](0005-append-only-stock-ledger.md)).

## Alternatives considered

- **A `code` or `slug` column on each reference table** (`documents.code = 'initial_inspection'`,
  `main_warehouses.is_default`). It survives databases whose ids differ, and a review of the 1.x code suggested it.
  It needs a unique column, a backfill and a lookup by code on each table; the ids were already
  referenced by foreign keys and by `MedicalScale`, and named constants give the same readability.
  A code column remains the better choice if the application ever has to merge data from several
  installations.
- **Looking rows up by name.** The original approach; it breaks as soon as a row is renamed on its
  screen.
- **Ids in configuration.** It moves the magic numbers into `.env` without making them safer, and a
  test database would need the same configuration.
- **Seeding the reference rows once per test run with `DatabaseMigrations` instead of
  `RefreshDatabase`.** Much slower, since every test would migrate the schema again.
