<?php

namespace App\Imports;

use App\Models\Diagnosis;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DiagnosisImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Diagnosis::create([
                'name' => $row['nazvanie'],
            ]);
        }
    }
}
