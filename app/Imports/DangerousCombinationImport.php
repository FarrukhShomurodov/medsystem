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
                'drug1_en' => $row['drug1'],
                'drug1_ru' => $row['preparat_1'],
                'drug2_en' => $row['drug2'],
                'drug2_ru' => $row['preparat_2'],
                'severity_en' => $row['severity'],
                'severity_ru' => $row['severity'],
                'mechanism_en' => $row['tiazest'],
                'mechanism_ru' => $row['tiazest'],
                'comment_en' => $row['interactioncomment'],
                'comment_ru' => $row['vzaimodeistviekommentarii'],
            ]);
        }
    }
}
