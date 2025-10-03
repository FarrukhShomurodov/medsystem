<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CashierStatsExport implements FromView
{
    protected $records;
    protected $total;

    public function __construct($records, $total)
    {
        $this->records = $records;
        $this->total = $total;
    }

    public function view(): View
    {
        return view('admin.exports.cashier', [
            'records' => $this->records,
            'total' => $this->total,
        ]);
    }
}
