<?php

namespace App\Imports\Concerns;

use App\Imports\ImportReport;
use Maatwebsite\Excel\Concerns\SkipsFailures;

/**
 * For imports that skip invalid rows: counts the saved rows and reports both.
 */
trait ReportsResult
{
    use SkipsFailures;

    private int $imported = 0;

    public function report(): ImportReport
    {
        return new ImportReport($this->imported, array_values($this->failures()->all()));
    }
}
