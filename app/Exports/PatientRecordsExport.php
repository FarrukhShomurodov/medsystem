<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PatientRecordsExport implements FromView
{
    protected $allRecords;
    protected $total;

    public function __construct($allRecords, $total)
    {
        $this->allRecords = $allRecords;
        $this->total = $total;
    }

    public function view(): View
    {
        return view('admin.exports.patient_records', [
            'allRecords' => $this->allRecords,
            'total' => $this->total,
        ]);
    }
}
