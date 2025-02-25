<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name',
            'description',
            'warehouse_type_id',
            'address',

            //
            'medication*',
            'medication.quantity',
            'medication.expiration_date',
            'medication.date_of_receipt',
            'medication.batch_number',

            'total_volume',
            'occupied_volume',
            'occupied_volume_percentage',
            'max_capacity',

            'phone',
            'email',
            'work_time',
            'head_name',
            'head_position',
            'head_contact',

            'date_of_last_inventory',
            'result',

            // Условия хранения
            'temperature',
            'humidity',
            'light_protected',

            // Особенности хранения
            'is_dangerous',
            'refrigeration',
            'freezing',
            'flammable',

            // Местоположение товаров
            'shelf_location',
            'special_zone',
            'label',

            'supplier_id',
            'transport',
            'invoices',
            'quality_certificates',
            'acceptance_certificates',
        ];
    }
}
