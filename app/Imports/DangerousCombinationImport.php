<?php

namespace App\Imports;

use App\Models\DangerousCombination;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DangerousCombinationImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $rows->forget(0);

        foreach ($rows as $row) {
            DangerousCombination::create([
                'drug1_en'       => $row[0],
                'drug1_ru'       => $row[1],
                'drug2_en'       => $row[2],
                'drug2_ru'       => $row[3],
                'severity_en'    => $row[4],
                'severity_ru'    => $row[5],
                'mechanism_en'   => $row[6],
                'mechanism_ru'   => $row[7],
                'comment_en'     => $row[8],
                'comment_ru'     => $row[9],
            ]);
        }
    }
}
