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
            $possibleCodes = ['kod', 1, 2, 3, 4, 5];

            $code = null;
            foreach ($possibleCodes as $key) {
                if (!empty($row[$key])) {
                    $code = $row[$key];
                    break;
                }
            }

            Diagnosis::create([
                'code' => $code,
                'name' => $row['nazvanie'],
            ]);
        }
    }
}
