<?php

namespace App\Imports;

use App\Models\Service;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServicesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Service::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'code' => $row['code'],
                'price' => $row['price'],
                'discount' => $row['discount'],
                'currency' => $row['currency'],
                'service_type_id' => $row['service_type_id'],
                'service_group_id' => $row['service_group_id'],
                'is_active' => $row['is_active'] ?? false,
                'is_appointment' => $row['is_appointment'] ?? false,
            ]);
        }
    }
}
