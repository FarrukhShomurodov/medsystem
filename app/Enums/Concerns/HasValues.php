<?php

namespace App\Enums\Concerns;

use App\Enums\Contracts\HasLabel;

/**
 * Helpers shared by the string-backed enums of app/Enums.
 *
 * @phpstan-require-implements \BackedEnum
 * @phpstan-require-implements HasLabel
 */
trait HasValues
{
    /**
     * The raw values, in declaration order. Handy for validation rules and
     * for database CHECK constraints that must stay in sync with the enum.
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Value => Russian label, for <select> options.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            self::values(),
            array_map(fn (self $case): string => $case->label(), self::cases()),
        );
    }
}
