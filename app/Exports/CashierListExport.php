<?php

namespace App\Exports;

use App\Queries\Billing\BillableItem;
use App\Queries\Billing\CashierFilter;
use App\Queries\Billing\CashierItemsQuery;
use App\Services\Billing\CurrencyTotals;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * The cashier list, with the filters of the page applied: one row per order, then one total row
 * per currency. The rows are written as plain values, a chunk of orders at a time
 * (CashierItemsQuery::lazy()), with fixed column widths: no HTML table to render and parse back,
 * and no pass over every cell to measure it. CashierController caps how many orders one file holds.
 */
final readonly class CashierListExport implements FromGenerator, WithColumnWidths, WithCustomValueBinder, WithHeadings, WithStyles
{
    public function __construct(
        private CashierItemsQuery $items,
        public CashierFilter $filter,
        public CurrencyTotals $totals,
    ) {}

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return ['ID', 'Тип', 'Услуга', 'Пациент', 'Ответственный', 'Цена', 'Валюта', 'Дата и время', 'Статус'];
    }

    /**
     * @return Generator<int, list<int|string|null>>
     */
    public function generator(): Generator
    {
        /** @var BillableItem $item */
        foreach ($this->items->lazy($this->filter) as $item) {
            yield [
                $item->id,
                $item->type->label(),
                $item->name,
                $item->patientName,
                $item->staffName ?? '—',
                $item->price->toDecimal(),
                $item->currency,
                $item->occurredAt,
                $item->status->label(),
            ];
        }

        foreach ($this->totals->all() as $currency => $sum) {
            yield ['ИТОГО:', null, null, null, null, $sum->toDecimal(), $currency, null, null];
        }
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        return ['A' => 10, 'B' => 14, 'C' => 40, 'D' => 36, 'E' => 32, 'F' => 14, 'G' => 9, 'H' => 18, 'I' => 16];
    }

    /**
     * The headings and the total rows (the last rows of the sheet) in bold.
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $bold = ['font' => ['bold' => true]];
        $last = $sheet->getHighestRow();

        return array_fill_keys([1, ...range($last - count($this->totals->all()) + 1, $last)], $bold);
    }

    /** Names and currencies are free text: never a formula (TextSafeValueBinder). */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        return (new TextSafeValueBinder)->bindValue($cell, $value);
    }
}
