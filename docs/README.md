# Documentation

The project's entry point is the [README](../README.md) ([на русском](../README.ru.md)). This
folder holds everything that does not fit there.

## Architecture

| Document | What it covers |
|---|---|
| [architecture/overview.md](architecture/overview.md) | System context, module map, layers, and one request (the cashier payment) traced end to end |
| [architecture/folder-layout.md](architecture/folder-layout.md) | Where each kind of class goes, module by module, and what is where today |
| [architecture/domain.md](architecture/domain.md) | The business rules per module, where the code enforces them and which test proves them, with the status lifecycles as state diagrams |
| [architecture/data-model.md](architecture/data-model.md) | ER diagrams of the core tables, foreign key rules, CHECK, exclusion and unique constraints, and the fixed-id reference rows |
| [architecture/security.md](architecture/security.md) | Authentication, a permission on every route, policies, CSRF, XSS, security headers, private files, input hardening, logging |
| [architecture/testing.md](architecture/testing.md) | Test layout and helpers, the PostgreSQL test database, parallel runs, the commands and what CI runs |
| [architecture/frontend.md](architecture/frontend.md) | Tabler and Vite, the widgets a page marks up, how a page and its script are written |

Read the overview first, then the domain rules. What the code does not handle yet is listed at
the end of the domain page, under [Known gaps](architecture/domain.md#known-gaps).

## Architecture decision records

The index with a dependency diagram is in [adr/README.md](adr/README.md).

| ADR | Decision |
|---|---|
| [0001](adr/0001-postgresql-as-the-only-database.md) | PostgreSQL as the only supported database |
| [0002](adr/0002-workflow-services-read-models-and-form-requests.md) | Workflow services by module, read models in queries, typed form requests |
| [0003](adr/0003-backed-enums-and-morph-aliases.md) | Stored vocabularies as backed enums with declared lifecycles, morph aliases in polymorphic columns |
| [0004](adr/0004-row-locks-in-a-fixed-order.md) | Pessimistic row locks in a fixed order, with database constraints as the backstop |
| [0005](adr/0005-append-only-stock-ledger.md) | An append-only stock ledger as the single writer of stock, with FEFO batches |
| [0006](adr/0006-money-in-minor-units-with-per-currency-totals.md) | Money as integer minor units in PHP, decimal columns, totals per currency |
| [0007](adr/0007-orders-keep-their-price-and-currency.md) | Orders keep the price and currency they were placed at |
| [0008](adr/0008-authorization-permissions-positions-and-policies.md) | A permission on every route, roles derived from staff positions, policies for record-level rules |
| [0009](adr/0009-restrict-deletes-and-retain-records.md) | Restricted deletes and retained records instead of cascades or soft deletes |
| [0010](adr/0010-tabler-and-vite-instead-of-a-commercial-template.md) | Tabler and Vite instead of the commercial template |
| [0011](adr/0011-quality-gates.md) | Quality gates: Pest on PostgreSQL, Larastan level 8 without a baseline, Rector, Pint, arch tests |
| [0012](adr/0012-fixed-id-reference-data-and-idempotent-seeders.md) | Reference data with fixed ids named in code, idempotent seeders |

## Working on the project

| Document | What it covers |
|---|---|
| [CONTRIBUTING.md](../CONTRIBUTING.md) | Setup, branch and commit conventions, the checks to run before a pull request |
| [SECURITY.md](../SECURITY.md) | Supported version and how to report a vulnerability |
| [CHANGELOG.md](../CHANGELOG.md) | What changed between releases |

## Screenshots

The screenshots in [screenshots/](screenshots/) are taken at 1440×900 over the demo data. The UI is
in Russian.

| Screenshot | Page and what it is for |
|---|---|
| [login.png](screenshots/login.png) | Sign-in: the only page open to guests |
| [dashboard.png](screenshots/dashboard.png) | Home: the day in the hospital, with one card per page the account may open |
| [patients.png](screenshots/patients.png) | Patient list: search by name and date of birth, filters by the department and the doctor of the open stay |
| [patient-card.png](screenshots/patient-card.png) | Patient card: order buttons, dangerous-combination warnings for the prescriptions in progress, visit history |
| [appointment.png](screenshots/appointment.png) | Appointment booking: pick a service, then a free slot in today's schedule of a doctor who offers it |
| [doctor-calendar.png](screenshots/doctor-calendar.png) | A doctor's weekly calendar with free, booked and past slots |
| [cashier.png](screenshots/cashier.png) | Cashier: every kind of order waiting for payment, with prices in UZS and statuses |
| [nurse-window.png](screenshots/nurse-window.png) | Nurse window: the inpatients of the department and their vital-sign charts |
| [stock-remains.png](screenshots/stock-remains.png) | Main warehouse remains, batch by batch, with expiry warnings |
| [staff.png](screenshots/staff.png) | Staff list with today's working status |
| [error-404.png](screenshots/error-404.png) | The Russian 404 page: a standalone error page (`resources/views/errors/layout.blade.php`) with a link back to the dashboard |
