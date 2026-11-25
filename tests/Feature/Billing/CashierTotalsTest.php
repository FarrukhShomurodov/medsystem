<?php

use App\Enums\ServiceStatus;
use App\Models\PatientMedication;
use App\Models\PatientProcedure;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\Reception;
use App\Models\Service;
use App\Queries\Billing\CashierFilter;
use App\Queries\Billing\CashierItemsQuery;
use App\Services\Billing\Amount;
use App\Services\Billing\CurrencyTotals;
use App\Services\Billing\PaymentState;

/*
| CashierItemsQuery::totals(): the sums of a cashier list, added up by the database (the dashboard
| shows them without loading the list). They must equal what the loaded list adds up to.
*/

/**
 * Currency => minor units, sorted by currency.
 *
 * @param  array<string, Amount>  $sums
 * @return array<string, int>
 */
function minorUnitsByCurrency(array $sums): array
{
    $units = array_map(fn (Amount $sum): int => $sum->minorUnits, $sums);
    ksort($units);

    return $units;
}

it('adds up each list as its loaded orders do, the price list filling the prices orders lack', function (): void {
    // Kept prices and currencies, and orders written before they were kept: the price list's
    // price, and its currency code trimmed and in upper case.
    $dollars = Service::factory()->create(['price' => '35.50', 'currency' => ' usd ']);
    $kept = PatientService::factory()->confirmed()->create(['price' => '100000.50', 'currency' => 'UZS']);
    PatientService::factory()->confirmed()->for($dollars)->create(['price' => null, 'currency' => null]);
    Reception::factory()->confirmed()->for(Service::factory()->appointment()->state(['price' => '150000.00', 'currency' => null]))
        ->create(['price' => null, 'currency' => null]);
    PatientProcedure::factory()->confirmed()->for(Procedure::factory()->state(['price' => '20000.00']))->create(['price' => null]);
    PatientMedication::factory()->sentToFloor()->create(['price' => '7500.25']);
    PatientService::factory()->create(['status' => ServiceStatus::Paid, 'price' => '12.00', 'currency' => 'EUR']);
    PatientService::factory()->confirmed()->create(['service_date' => today()->subDay()->toDateString(), 'price' => '1.00', 'currency' => 'UZS']);

    $query = app(CashierItemsQuery::class);
    $filters = [
        'to pay' => new CashierFilter,
        'paid' => new CashierFilter(state: PaymentState::Paid),
        'to pay today' => new CashierFilter(date: today()->toImmutable()),
        'one patient' => new CashierFilter(lastName: $kept->patient?->last_name),
        'nothing' => new CashierFilter(firstName: 'нет такого пациента'),
    ];

    foreach ($filters as $name => $filter) {
        $items = $query->lazy($filter)->collect();
        $totals = $query->totals($filter);

        expect($totals->orders)->toBe($items->count(), $name)
            ->and(minorUnitsByCurrency($totals->sums->all()))->toBe(minorUnitsByCurrency(CurrencyTotals::of($items)->all()), $name);
    }

    // 100 000,50 + 150 000 + 20 000 + 7 500,25 + 1 in sums, and 35,50 dollars.
    expect(minorUnitsByCurrency($query->totals(new CashierFilter)->sums->all()))->toBe(['USD' => 3_550, 'UZS' => 27_750_175]);
});

it('lists the default currency first, then the others in alphabetical order', function (): void {
    PatientService::factory()->confirmed()->create(['price' => '1.00', 'currency' => 'USD']);
    PatientService::factory()->confirmed()->create(['price' => '1.00', 'currency' => 'EUR']);
    PatientService::factory()->confirmed()->create(['price' => '1.00', 'currency' => 'UZS']);

    expect(array_keys(app(CashierItemsQuery::class)->totals(new CashierFilter)->sums->all()))->toBe(['UZS', 'EUR', 'USD']);
});
