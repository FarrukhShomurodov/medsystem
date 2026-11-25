<?php

namespace App\Imports;

use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Validators\Failure;

/**
 * What an Excel import did: the rows it saved and the rows it skipped, with the reasons.
 */
final readonly class ImportReport
{
    /** Skipped rows listed by name in the flash message; the rest are counted. */
    private const int LISTED_ROWS = 10;

    /**
     * @param  list<Failure>  $failures
     */
    public function __construct(
        public int $imported,
        public array $failures,
    ) {}

    public function flashTo(RedirectResponse $redirect): RedirectResponse
    {
        if ($this->imported === 0 && $this->failures === []) {
            return $redirect->with('warning', 'В файле нет строк для импорта.');
        }

        $redirect->with('success', "Импортировано строк: {$this->imported}.");

        return $this->failures === [] ? $redirect : $redirect->with('warning', $this->skippedRows());
    }

    private function skippedRows(): string
    {
        $reasons = collect($this->failures)
            ->groupBy(fn (Failure $failure): int => $failure->row())
            ->sortKeys()
            ->map(fn ($failures, int $row): string => "строка {$row}: ".$failures->flatMap(fn (Failure $failure): array => $failure->errors())->implode(' '));

        $message = 'Пропущено строк: '.$reasons->count().'. '.$reasons->take(self::LISTED_ROWS)->implode('; ');

        return $reasons->count() > self::LISTED_ROWS
            ? $message.'; и ещё '.($reasons->count() - self::LISTED_ROWS).'.'
            : $message.'.';
    }
}
