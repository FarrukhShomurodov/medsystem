<?php

namespace App\Imports;

use App\Exceptions\ServiceImportValidationException;
use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ServicesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (
                !isset($row['naimenovanie']) ||
                !isset($row['stoimost']) ||
                !isset($row['tip_id']) ||
                !isset($row['gruppa_id'])
            ) {
                throw new ServiceImportValidationException('Заполните все данные!');
            }

            if (
                !is_numeric($row['stoimost']) ||
                !is_numeric($row['skidka']) ||
                !is_numeric($row['tip_id']) ||
                !is_numeric($row['gruppa_id'])
            ) {
                throw new ServiceImportValidationException('Неверный тип данных!');
            }

            if (is_numeric($row['tip_id']) && !ServiceType::query()->where('id', is_numeric($row['tip_id']))->exists(
                )) {
                throw new ServiceImportValidationException('Такого типа не существует!');
            }

            if (is_numeric($row['gruppa_id']) && !ServiceGroup::query()->where(
                    'id',
                    is_numeric($row['group_id'])
                )->exists()) {
                throw new ServiceImportValidationException('Такой группы не существует!');
            }

            Service::create([
                'name' => $row['naimenovanie'],
                'description' => $row['opisanie'],
                'code' => $row['kod'],
                'price' => $row['stoimost'],
                'discount' => $row['skidka'],
                'currency' => $row['valiuta'],
                'service_type_id' => $row['tip_id'],
                'service_group_id' => $row['gruppa_id'],
                'is_active' => $row['aktivna'] ?? false,
                'is_appointment' => $row['po_zapisi'] ?? false,
            ]);
        }
    }
}
