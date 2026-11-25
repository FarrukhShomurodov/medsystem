# 9. Restricted deletes and retained records instead of cascades or soft deletes

- Status: Accepted
- Date: 2025-11

## Context

Every foreign key of the original schema was `ON DELETE CASCADE`. Deleting a lookup row (a region,
a staff member, a service from the price list, a medication) silently removed the patients,
examination maps, prescriptions or stock rows that pointed at it. Deleting a department warehouse
or a pharmacy removed its stock without a write-off, leaving the stock ledger pointing at rows that
no longer existed. Deleting a patient or a stay erased paid orders and dispensed prescriptions: the
records the cash desk and the ledger depend on.

At the same time a patient registered by mistake, with nothing done for them yet, must still be
removable.

## Decision

1. **Foreign keys to reference data restrict the delete**, or set the column to `NULL` where it is
   optional (`2025_11_18_100600_restrict_deletes_on_reference_foreign_keys`): orders keep their
   service, procedure, medication and staff member; stock rows keep their item; appointments keep
   their doctor. A warehouse or pharmacy that holds or ordered stock cannot be deleted
   (`2025_11_19_100300_restrict_deletes_of_places_holding_stock`). Cascades stay only where the
   child belongs to its parent: answers of a document, contacts of an employee, the records of a
   patient or of a stay.
2. **A restricted delete is a user mistake, not a crash.** `bootstrap/app.php` answers a
   `QueryException` with SQLSTATE `23503` or `23001` on a `DELETE` request with
   "Запись нельзя удалить: она используется в других разделах." as a flash message, or as JSON 409
   on an AJAX route, and does not report it.
3. **Patients and stays keep their cascade, guarded by `App\Services\Patients\RetainedRecords`.**
   Inside the deleting transaction it refuses the delete while the patient or the stay holds:
   - a service or procedure order that was paid (paid, in_process, completed), or an appointment
     that was paid or done;
   - a prescription whose drug left the warehouse (sent_to_floor, received_at_station, completed,
     returned), because the ledger's movements point at it;
   - for a patient, a closed stay, which is a finished medical record.

   The refusal is a `DeletionRefusedException` that lists what is kept, shown as a Russian flash
   message. `RetainedRecords` locks every order of the owner in the cashier's order
   ([ADR 4](0004-row-locks-in-a-fixed-order.md)), so a payment taken at the same moment either
   waits and then finds nothing to pay, or commits first and is seen by the check.
4. **A single paid service order cannot be deleted** either (`PaidServiceException` in
   `PatientServiceAssignService::destroy()`).
5. **No model uses soft deletes.** A row is either in use and kept, or not in use and deleted.

## Consequences

### Positive

- The history the cash desk and the stock ledger depend on cannot disappear through a delete
  button, whichever screen it is on.
- A delete that is refused explains itself instead of returning a 500, and the record stays as it
  was (`RestrictedDeleteTest`, `RecordDeletionTest`).
- Reference data nobody uses can still be deleted, and so can a patient registered by mistake.
- Queries stay simple: there is no `deleted_at` condition to remember in every list, unique index
  and partial index.

### Negative

- For reference data the database answers after the fact: the delete button is shown, the user
  clicks it, and only the failed `DELETE` reveals that the row is in use. PostgreSQL also aborts the
  transaction after that failed statement, so code that wants to continue must use a savepoint, as
  the tests do.
- Retiring a catalogue entry that is still referenced needs an active flag on that catalogue;
  deleting it is not possible, by design.
- The statuses that make an order "retained" are listed in `RetainedRecords`, apart from the
  lifecycle enums of [ADR 3](0003-backed-enums-and-morph-aliases.md). A new paid-like status has to
  be added in both places.
- Filled-in medical documents and survey responses do not count as retained, so deleting a patient
  without paid or dispensed orders also deletes their assessments.

## Alternatives considered

- **Soft deletes.** Every query, unique index and partial index (one open stay per patient) would
  have to exclude deleted rows, and foreign keys would keep pointing at rows the application treats
  as gone. Nothing would be erased either, which is not what a mistaken registration needs.
- **Checking in PHP before every delete of reference data.** Each check would have to know every
  table that points at the row, and it would race with a concurrent insert. The foreign key already
  knows both and holds under concurrency.
- **Keeping the cascades and relying on a confirmation dialog.** The dialog cannot show what a
  cascade will take with it, and that was how paid orders were lost.
