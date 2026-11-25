<?php

use App\Enums\ServiceStatus;
use App\Queries\Billing\BillableItem;
use App\Services\Billing\Amount;
use App\Services\Billing\BillableType;
use App\Services\Billing\CurrencyTotals;

function billableItem(string $price, string $currency): BillableItem
{
    return new BillableItem(
        type: BillableType::Service,
        id: 1,
        patientId: 1,
        name: 'Анализ крови',
        patientName: 'Каримова Дильноза',
        staffName: null,
        price: Amount::fromDecimal($price),
        currency: $currency,
        occurredAt: '18.11.2026',
        status: ServiceStatus::Confirmed,
    );
}

it('parses decimal column values into exact minor units', function (string|int|null $value, int $minorUnits): void {
    expect(Amount::fromDecimal($value)->minorUnits)->toBe($minorUnits);
})->with([
    'two decimals' => ['150000.00', 15_000_000],
    'one decimal' => ['12.5', 1250],
    'integer' => [7, 700],
    'trailing zeros' => ['3.1000', 310],
    'negative' => ['-0.05', -5],
    'missing price' => [null, 0],
    'largest decimal(14,2)' => ['999999999999.99', 99_999_999_999_999],
]);

it('refuses values that are not money with at most two decimals', function (string $value): void {
    Amount::fromDecimal($value);
})->throws(InvalidArgumentException::class)->with(['12.345', '1e3', 'abc', '1,50']);

it('adds amounts without the rounding errors of floats', function (): void {
    $sum = Amount::zero();

    foreach (range(1, 10) as $ignored) {
        $sum = $sum->plus(Amount::fromDecimal('0.10'));
    }

    expect($sum->toDecimal())->toBe('1.00')
        ->and(Amount::fromDecimal('0.1')->plus(Amount::fromDecimal('0.2'))->toDecimal())->toBe('0.30');
});

it('formats amounts plainly for spreadsheets and grouped for the screen', function (): void {
    $amount = Amount::fromDecimal('1250000.5');

    expect($amount->toDecimal())->toBe('1250000.50')
        ->and($amount->format())->toBe("1\u{00A0}250\u{00A0}000,50")
        ->and(Amount::fromDecimal('-0.05')->toDecimal())->toBe('-0.05')
        ->and(Amount::zero()->format())->toBe('0,00');
});

it('sums prices per currency and never adds different currencies together', function (): void {
    $totals = CurrencyTotals::of([
        billableItem('150000.00', 'UZS'),
        billableItem('20.50', 'USD'),
        billableItem('0.10', 'UZS'),
    ]);

    expect(array_map(fn (Amount $sum): string => $sum->toDecimal(), $totals->all()))->toBe(['UZS' => '150000.10', 'USD' => '20.50'])
        ->and($totals->format())->toBe("150\u{00A0}000,10 UZS + 20,50 USD");
});

it('shows nothing to pay as zero in the default currency', function (): void {
    $totals = CurrencyTotals::of([]);

    expect(array_map(fn (Amount $sum): string => $sum->toDecimal(), $totals->all()))->toBe(['UZS' => '0.00'])
        ->and($totals->format())->toBe('0,00 UZS');
});
