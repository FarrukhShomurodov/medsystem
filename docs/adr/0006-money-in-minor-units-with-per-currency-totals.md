# 6. Money as integer minor units in PHP, decimal columns in the database, totals per currency

- Status: Accepted
- Date: 2025-11

## Context

Prices are in Uzbek sum (UZS) by default, where a consultation costs hundreds of thousands and a
piece of equipment hundreds of millions. Laravel's `decimal()` defaults to `(8, 2)`, which caps a
value at 999 999.99, and the retail and wholesale prices of medications were plain integers. The price list may also name another
currency for a service (USD, for example), and the cash desk, the registrar's basket and the exports
add up the prices of many orders.

Adding prices as PHP floats accumulates rounding errors, and adding the prices of different
currencies gives a number that means nothing.

## Decision

1. **Columns are `decimal(14, 2)`** for every money column (`2025_11_18_100300_widen_money_columns`);
   the per-unit purchase price keeps four decimals, `decimal(14, 4)`. Models cast them as
   `decimal:2` (`decimal:4` for that purchase price), which Eloquent hands over as numeric strings.
2. **In PHP, an amount is `App\Services\Billing\Amount`**, a `final readonly` value object holding
   an `int` of minor units (tiyin, cents):
   - `Amount::fromDecimal()` parses the column's string exactly with a regular expression, never
     through a float; a float is accepted only as the fallback for an uncast value and rounded once;
   - `plus()` adds two amounts in integers;
   - `toDecimal()` gives the plain `150000.00` for spreadsheet cells, `format()` the Russian
     `150 000,00` with non-breaking spaces for the screen.
3. **Multiplication uses `brick/math`.** A prescription's price is the unit price of the latest
   main-warehouse batch times the units, computed with `BigDecimal` and rounded half-up to two
   decimals (`PatientMedicationService`).
4. **Totals are kept per currency and never added across currencies.** `CurrencyTotals::of()` sums
   the `BillableItem`s of a list by their currency code and prints `150 000,00 UZS + 20,00 USD`;
   an empty list is zero in the default currency, `BillableItem::DEFAULT_CURRENCY` (UZS). A
   currency code is normalised once, by `Service::priceCurrency()`: trimmed, upper case, UZS when
   the price list names none.
5. **The database may sum, the result still becomes an `Amount`.** `CashierItemsQuery::totals()`
   adds up the cashier's list per currency in one SQL query and converts each sum with
   `Amount::fromDecimal()` into `CurrencyTotals::fromSums()`. `CashierTotalsTest` holds the SQL
   totals equal to `CurrencyTotals::of()` over the loaded list.
6. **There is no currency conversion anywhere.** The application shows what is owed in each
   currency; exchange is outside its scope.

The cashier page ([docs/screenshots/cashier.png](../screenshots/cashier.png)) lists the unpaid
orders of all kinds with their prices and statuses; its totals and the dashboard's cash tiles are
built this way.

## Consequences

### Positive

- Sums are exact: `tests/Unit/Billing/MoneyTest.php` checks parsing into minor units, adding
  without float errors, both output formats and the per-currency totals.
- The columns stay readable in SQL, in backups and in ad hoc reports, and PostgreSQL's `numeric`
  sums them exactly when a list is aggregated in the database.
- A total can never silently mix currencies: the only way to add amounts of a list is through
  `CurrencyTotals`, keyed by currency.
- 64-bit minor units hold far more than the largest `decimal(14, 2)` value, so `Amount` cannot
  overflow on any stored price.

### Negative

- Money has two representations: a decimal string in the models and the database, an `Amount` in
  billing code, screens and exports. Code outside billing (equipment prices, purchase prices of a
  receipt) still handles the decimal strings directly.
- `Amount` carries no currency. The currency travels beside it (`BillableItem::$currency`), so
  keeping currencies apart is the job of `CurrencyTotals`, not of the type.
- The price fallback rule (kept price, else the price list's) exists twice, in PHP
  (`BillableItemLoader`) and in SQL (`CashierItemsQuery`), held equal by a test rather than by
  construction.
- `services.discount` is stored but not applied by billing.

## Alternatives considered

- **`bigint` minor-unit columns.** Exact as well, but every SQL report, export and manual query
  would have to divide by 100, and the existing decimal columns would need a data migration. The
  conversion happens once, at the boundary, in `Amount::fromDecimal()`.
- **Floats with rounding at display time.** Rejected: errors accumulate in long lists, and the sum
  shown would depend on the order of addition.
- **A money library (`moneyphp/money`, `brick/money`).** Both pair an amount with its currency. The
  billing code needs addition, two formats and per-currency grouping; a small value object covers
  that without another dependency, and `brick/math`, already installed with Laravel, handles the one
  multiplication.
- **Converting every currency into UZS for one grand total.** It needs an exchange rate source and
  a rule for which rate applies when; the hospital collects each currency as it is billed.
