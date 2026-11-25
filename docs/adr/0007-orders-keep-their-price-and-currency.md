# 7. Orders keep the price and currency they were placed at

- Status: Accepted
- Date: 2025-11

## Context

A patient's orders are of four kinds: services (`patient_services`), procedures
(`patient_procedures`), appointments (`receptions`) and prescriptions (`patient_medications`). The
registrar confirms them and the cashier takes payment later, sometimes days later for a hospital
stay.

Prescriptions already stored their price. The other three kinds were billed at the current price of
the price list, so editing a price after an order was placed changed what the cash desk charged for
it, and a printed or exported basket could not be reproduced. The first fix stored the price but
still read the currency from the price list, so moving a service from UZS to USD then billed an
earlier 150 000 UZS order as 150 000 USD.

## Decision

1. **An order stores its price and the price's currency when it is placed.** Two additive
   migrations add nullable columns:
   - `2025_11_20_120000_add_price_to_patient_orders`: `price decimal(14, 2)` on `patient_services`,
     `patient_procedures` and `receptions`;
   - `2025_11_20_130000_add_currency_to_patient_orders`: `currency` on `patient_services` and
     `receptions`. Procedures have no currency in the price list and are always billed in the
     default currency.
2. **The services write it at order time**, from the price list entry as billing reads it
   (`Service::priceCurrency()` normalises the code): `PatientServiceAssignService` (on creation, and
   again when an unpaid order is moved to another service), `PatientProcedureService`, and
   `ReceptionService::book()`. A prescription's price is computed when it is prescribed
   ([ADR 6](0006-money-in-minor-units-with-per-currency-totals.md)). The factories store the price
   the same way, so factory-made orders behave like real ones.
3. **Billing charges the kept price.** `BillableItemLoader::priceOf()` and `currencyOf()` read the
   order's own columns and fall back to the price list only when they are `NULL`, for a row written
   by an older application instance during a deploy. `CashierItemsQuery::totals()` writes the same
   rule in SQL with `coalesce()`.
4. **Existing orders were backfilled** with the current price list price and its normalised
   currency, updating only rows still `NULL`, so the migrations are safe to run again; `down()`
   drops the columns.
5. **A paid order keeps its price.** Service orders are the only kind that can be edited after they
   are placed, and `PatientServiceAssignService` refuses to move a paid one to another service or
   patient, or to delete it (`PaidServiceException`). Procedure orders and appointments have no edit
   path at all, only status changes.

`tests/Feature/Billing/OrderPriceTest.php` covers each step: orders are charged at the price they were
ordered at, a reassigned unpaid order takes the new service's price, orders without a kept price use
the price list, and both backfills run and roll back.

## Consequences

### Positive

- What the cash desk charges is fixed when the doctor or registrar places the order; editing the
  price list changes only future orders.
- The registrar's basket, the cashier list, the dashboard tiles and the Excel exports agree, because
  they all read the same kept values.
- The migrations are additive and idempotent, so they run on an existing database without
  rebuilding it, and a repeated run changes nothing.

### Negative

- The backfilled prices are the price list's at the time of the migration, not the true historic
  prices, which were never recorded. Orders placed before the change are only as accurate as that.
- The columns are nullable and billing keeps a fallback, so there are two code paths (kept value,
  price list) in PHP and in SQL. `CashierTotalsTest` keeps them equal, but the fallback can only be
  removed once no `NULL` is left and the columns are made `NOT NULL`.
- Moving an unpaid order to another service re-prices it at that service's current price, and a
  confirmed order stays confirmed: the new price reaches the cashier without a second registrar
  confirmation.
- There is still no payment record. "Paid" is a status of the order, and the order keeps only the
  moment it was paid (`paid_at`, added later so the paid list counts a payment on its own day); the
  amount actually collected, the method and the cashier are not stored, and a returned paid
  prescription is not refunded at the cash desk.

## Alternatives considered

- **A price history table with effective dates.** Billing would look up the price valid on the
  order's date. It needs a history that was never kept and a lookup on every bill, and it still
  breaks if a historic row is edited; storing the value on the order is simpler and final.
- **Invoices or a payments table with line items.** The right model for collected money, refunds and
  cash reconciliation, and the next step for billing. It is a larger change to the cashier's
  workflow than this refactor took on; the snapshot columns are what such lines would copy.
- **`NOT NULL` columns from the start.** Rows written by an old instance during the deploy would have
  failed to insert. The nullable column with a fallback lets the code and the schema change in
  either order.
