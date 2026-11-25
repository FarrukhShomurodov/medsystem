<?php

namespace App\Support;

use App\Services\Billing\Amount;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * The Russian form in which the views print dates, ages and amounts, so that one kind of value
 * reads the same on every page: "18.11.2026", "18.11.2026 19:06", "62 года", "150 000,00". A
 * missing value prints as a dash. Form fields keep the ISO form their inputs expect.
 */
final class Display
{
    public const string NONE = '—';

    /** "18.11.2026". Date columns arrive as Carbon or, without a cast, as "Y-m-d" strings. */
    public static function date(CarbonInterface|string|null $value): string
    {
        return self::parse($value)?->format('d.m.Y') ?? self::NONE;
    }

    /** "18.11.2026 19:06". */
    public static function dateTime(CarbonInterface|string|null $value): string
    {
        return self::parse($value)?->format('d.m.Y H:i') ?? self::NONE;
    }

    /** "1 год", "2 года", "62 года", "5 лет": full years on the given day (today by default). */
    public static function age(CarbonInterface|string|null $birthDate, ?CarbonInterface $on = null): string
    {
        $born = self::parse($birthDate);

        if ($born === null) {
            return self::NONE;
        }

        $years = (int) $born->diffInYears($on ?? today());

        return $years.' '.trans_choice('год|года|лет', $years);
    }

    /** "150 000,00": a decimal(14,2) money column, as Amount::format() prints it. */
    public static function money(string|int|float|null $value): string
    {
        return $value === null || $value === '' ? self::NONE : Amount::fromDecimal($value)->format();
    }

    /**
     * A price per unit, kept with up to four decimals (decimal(14,4)): "24 000,00", "1 234,5678".
     * Amount refuses more than two decimals, so this one is formatted on its own.
     */
    public static function unitPrice(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return self::NONE;
        }

        $formatted = number_format((float) $value, 4, ',', "\u{00A0}");

        // Two decimals at least, like every other amount; the third and fourth only when they count.
        return preg_replace('/(,\d\d[1-9]?)0+$/', '$1', $formatted) ?? $formatted;
    }

    /** "25 %", "12,5 %". */
    public static function percent(string|int|float|null $value): string
    {
        if ($value === null || $value === '') {
            return self::NONE;
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 2, ',', "\u{00A0}"), '0'), ',');

        return $formatted."\u{00A0}%";
    }

    private static function parse(CarbonInterface|string|null $value): ?CarbonInterface
    {
        return $value === null || $value === '' ? null : ($value instanceof CarbonInterface ? $value : Carbon::parse($value));
    }
}
