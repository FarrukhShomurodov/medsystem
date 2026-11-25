# Folder layout

The application is organised by module where it runs a workflow, and stays flat where it only
saves the form of a record. The modules are the hospital's workflows:

| Module       | What it covers                                                                  |
|--------------|---------------------------------------------------------------------------------|
| `Billing`    | Registrar confirmation of a patient's orders, cashier payments, money amounts   |
| `Clinical`   | Orders for a patient (services, prescriptions), medical documents and scores    |
| `Patients`   | The patient card, its history, examination maps (hospital stays)                |
| `Scheduling` | Staff and their accounts, schedules, appointment slots, reception booking       |
| `Stock`      | The stock ledger, main warehouse receipts, supply requests, transfers           |

How the modules work together is in [overview.md](overview.md). The rules each of them enforces
are in [domain.md](domain.md).

## Where a class goes

| Kind of class                                                            | Folder                                         |
|--------------------------------------------------------------------------|------------------------------------------------|
| Workflow logic of a module: status transitions, stock or money movements, booking rules | `app/Services/<Module>/`  |
| Value objects and enums of that logic, never stored (`Amount`, `CurrencyTotals`, `BillableType`, `TimeRange`, `SlotGrid`); its queries use them too | next to the service, `app/Services/<Module>/` |
| Read models: queries and the shapes they return for a page or an export  | `app/Queries/<Module>/`                        |
| Helpers every query shares (`LikePattern`, the one way a search matches text) | `app/Queries/`                            |
| Exceptions a module throws to refuse an operation                        | `app/Exceptions/<Module>/`                     |
| Enums stored in a database column (`ServiceStatus`, `TransferStatus` ...) | `app/Enums/` (their contracts in `Contracts/`, shared traits in `Concerns/`) |
| Blade components drawn from such an enum (`<x-status-badge>`, `<x-status-icon>`) | `app/View/Components/`, `resources/views/components/` |
| The sidebar: its links, icons and the permission of each link (rendered by `<x-sidebar />`) | `app/Support/Navigation/` |
| Saving the form of a record (with its child rows, files and the checks that keep that record consistent) | `app/Services/<Model>Service.php` (flat) |
| Eloquent models                                                          | `app/Models/` (flat)                           |
| Authorization that depends on the record, beyond a permission (`AdminPolicy`) | `app/Policies/`, registered in `AppServiceProvider` |
| Dashboard pages / their AJAX endpoints                                   | `app/Http/Controllers/Dashboard/`, `.../Dashboard/Api/` |
| Form requests of the form that creates or edits a record, named after it (`StaffRequest`, `ReceptionRequest`) | `app/Http/Requests/` (flat, like the form services; the account forms in `Admin/`) |
| Form requests of a module's other actions: filters, status changes, decisions, payments, sign-in, readings | `app/Http/Requests/<Module>/` (`Billing/PayItemsRequest`, `Clinical/PatientServiceStatusRequest`, `Stock/RejectSupplyRequest`) |
| Excel imports and exports                                                | `app/Imports/`, `app/Exports/`                 |
| Artisan commands                                                         | `app/Console/Commands/` (discovered automatically) |

A flat service may refuse an operation with a module's exception (`StaffService` and
`AdminService` throw `Exceptions/Scheduling/AccountException`, `SurveyService` refuses to drop an
answered question). Once it takes part in a workflow (it changes a status, moves stock or money,
books time) it moves into that module's folder.

Tests mirror the modules: `tests/Feature/<Module>/` for behaviour through HTTP and the database,
`tests/Unit/<Module>/` for logic without them. Cross-cutting checks live in
`tests/Feature/Authorization`, `tests/Feature/Security`, `tests/Feature/Database` and
`tests/Feature/Frontend`. The enums, which several modules share, are tested in
`tests/Unit/Enums`. The dashboard home, which reads every module, is tested in
`tests/Feature/Dashboard`. `tests/Arch/ArchitectureTest.php` checks the rules of this page that can
be checked mechanically: namespaces and suffixes, and what services and read models may depend on.
Where a read model or a service lives otherwise follows this page by convention, not by a tool
check. See [testing.md](testing.md).

## The dashboard home

`DashboardSummary` computes, for one account, the cards of `DashboardCard` it may see. Each card links
to one page and needs that page's permission (the first of `permissions()`, the one its route checks;
`DashboardCardsTest` holds them equal), so a card is shown exactly to the accounts that may open its
page. A figure is read through the query of the page it links to, or by the same rule, so the card and
the page agree: `NursePatientsQuery::count()`, `CashierItemsQuery::totals()` over the cashier's filter
of the day, `RegistrarBasketQuery::queue()` over the registrar's baskets, the remains pages' expiry
warning (`medications.notify_before_days`). Every card costs a fixed number of queries.

## Statuses and other stored vocabularies

- A column with a closed set of values is cast to its enum in the model's `casts()` and documented
  with `@property`. Code compares enum cases (`$transfer->status === TransferStatus::Pending`),
  never the raw strings; Eloquent and the query builder take the cases as they are.
- Each value has its Russian `label()`; forms list `Enum::options()` and validate with `Rule::enum()`.
  A value shown as a badge or an icon implements `HasBadge` / `HasIcon` and is drawn by
  `<x-status-badge>` / `<x-status-icon>`, never by HTML built in PHP.
- The five lifecycles (`ServiceStatus`, `ReceptionStatus`, `PatientMedicationStatus`,
  `TransferStatus`, `SupplyRequestStatus`) declare one list, `nextStatuses()`; `HasLifecycle` derives
  `canTransitionTo()`, `isFinal()` and the final statuses from it. Services lock the row and check
  the transition before they write it.
- Polymorphic `*_type` columns store the aliases of the morph map in `AppServiceProvider`
  (`Relation::enforceMorphMap`), so a model must be listed there before it can be stored in one.

## What is where today

```
app/
  Console/Commands/        permissions:prune, documents:rescore, patient-files:privatize
  Enums/                   stored statuses and vocabularies (+ Contracts/HasLabel, HasBadge, HasIcon;
                           Concerns/HasValues, HasLifecycle)
  Exceptions/
    Billing/ Clinical/ Patients/ Scheduling/ Stock/
  Exports/  Imports/
  Http/
    Controllers/Dashboard/      pages
    Controllers/Dashboard/Api/  AJAX endpoints (routes/ajax.php, on the admin session)
    Middleware/                 SecurityHeaders, RejectMalformedInput, EnsureAccountIsActive,
                                ExaminationMapIsActive
    Requests/  Requests/{Admin,Auth,Billing,Clinical,Patients,Scheduling,Stock}/
  Models/
  Policies/                AdminPolicy, DocumentResponsePolicy, ReceptionPolicy
  Providers/               AppServiceProvider (policies, morph map, login limiter, pagination), TelescopeServiceProvider
  Queries/                 LikePattern
    Billing/    CashierItemsQuery (+ totals), RegistrarBasketQuery (+ the day's queue), BillableItem ...
    Dashboard/  DashboardSummary, DashboardCard: the home page's figures, card by card
    Patients/   PatientCardQuery, PatientListQuery, PatientPickerQuery, DiagnosisPickerQuery, PatientTimeline,
                NursePatientsQuery, VitalSignsChart
    Stock/      StockAlertsQuery (+ ExpiryWarnings): supply requests to decide, batches near expiry, items run out;
                RemainsQuery: the remains pages and the receipts page's running-low list, section by section
  Services/
    Billing/     CashierPaymentService, RegistrarConfirmationService, Amount, CurrencyTotals ...
    Clinical/    PatientMedicationService, PatientServiceAssignService, DocumentResponseService,
                 DangerousCombinationCheck, Scoring/ (DocumentScoreCalculator, MedicalScale ...)
    Patients/    ExaminationMapService, RecordPatientView, RetainedRecords
    Scheduling/  ReceptionService, AppointmentCalendar, SlotGrid, TimeRange
    Stock/       StockLedger, MainWarehouseService, SupplyRequestService, TransferService, StockSection ...
    *Service.php form services (AllergyService, CityService, PatientService, StaffService ...)
  Support/Authorization/   PermissionCatalog: every permission with its Russian label (seeded by PermissionSeeder)
  Support/Navigation/      Navigation, NavSection, NavItem: the sidebar's single definition
  Support/Display.php      the Russian form of dates, ages and amounts in the views
  View/Components/         Sidebar, StatusBadge, StatusIcon, OptionalVite
```

Routes: `routes/web.php` holds the dashboard pages, `routes/ajax.php` their AJAX endpoints
(mounted by `bootstrap/app.php` under `/api` in the `web` group, behind `auth:admin`).

## Related decisions

- [ADR 0002](../adr/0002-workflow-services-read-models-and-form-requests.md): Workflow services by module, read models in queries, form requests
- [ADR 0003](../adr/0003-backed-enums-and-morph-aliases.md): Backed enums and morph aliases
