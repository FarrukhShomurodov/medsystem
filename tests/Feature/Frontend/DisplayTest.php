<?php

use App\Support\Display;
use Illuminate\Support\Carbon;

/*
| The Russian form in which the views print dates, ages and amounts (App\Support\Display). The page
| sweep of AllPagesTest fails on any ISO date or decimal point left in the text of a page.
*/

it('prints dates and moments as d.m.Y, from Carbon or a date string, and a dash for none', function (): void {
    expect(Display::date(Carbon::parse('2026-11-18 19:06:26')))->toBe('18.11.2026')
        ->and(Display::date('1992-06-02'))->toBe('02.06.1992')
        ->and(Display::date(null))->toBe('—')
        ->and(Display::dateTime('2026-11-18 19:06:26'))->toBe('18.11.2026 19:06')
        ->and(Display::dateTime(''))->toBe('—');
});

it('prints an age in full years with the Russian plural', function (int $years, string $age): void {
    $today = Carbon::parse('2026-11-18');

    expect(Display::age($today->copy()->subYears($years)->toDateString(), $today))->toBe($age)
        // The day before the birthday the year is not full yet.
        ->and(Display::age($today->copy()->subYears($years)->addDay(), $today))->toBe(Display::age($today->copy()->subYears($years - 1), $today));
})->with([
    [1, '1 год'], [2, '2 года'], [4, '4 года'], [5, '5 лет'], [11, '11 лет'], [12, '12 лет'],
    [21, '21 год'], [22, '22 года'], [62, '62 года'], [82, '82 года'], [100, '100 лет'],
]);

it('prints money as Amount does, a price per unit with up to four decimals and a markup as a percentage', function (): void {
    expect(Display::money('51250.00'))->toBe("51\u{00A0}250,00")
        ->and(Display::money('0.50'))->toBe('0,50')
        ->and(Display::money(null))->toBe('—')
        ->and(Display::unitPrice('41000.0000'))->toBe("41\u{00A0}000,00")
        ->and(Display::unitPrice('1234.5678'))->toBe("1\u{00A0}234,5678")
        ->and(Display::unitPrice('12.3450'))->toBe('12,345')
        ->and(Display::unitPrice('12.3405'))->toBe('12,3405')
        ->and(Display::unitPrice(null))->toBe('—')
        ->and(Display::percent('25.00'))->toBe("25\u{00A0}%")
        ->and(Display::percent('12.50'))->toBe("12,5\u{00A0}%")
        ->and(Display::percent('100.00'))->toBe("100\u{00A0}%")
        ->and(Display::percent(null))->toBe('—');
});
