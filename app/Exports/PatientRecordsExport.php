<?php

namespace App\Exports;

use App\Queries\Billing\BillableItem;
use App\Services\Billing\CurrencyTotals;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

/**
 * The registrar's basket of one patient.
 */
final readonly class PatientRecordsExport implements FromView, ShouldAutoSize, WithCustomValueBinder
{
    /**
     * @param  Collection<int, BillableItem>  $items
     */
    public function __construct(
        public Collection $items,
        public CurrencyTotals $totals,
    ) {}

    public function view(): View
    {
        return view('admin.exports.patient_records', [
            'items' => $this->items,
            'totals' => $this->totals,
        ]);
    }

    /** Names and currencies are free text: never a formula (TextSafeValueBinder). */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        return (new TextSafeValueBinder)->bindValue($cell, $value);
    }
}
