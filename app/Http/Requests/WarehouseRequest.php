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
            'name' => 'required',
            'description' => 'required',
            'warehouse_type_id' => 'required',
            'address' => 'required',

            //
            'medications' => 'required|array',
//            'medications.*.id' => 'required|exists:medications,id',
//            'medications.*.quantity' => 'required|integer|min:1',
//            'medications.*.expiration_date' => 'required|date',
//            'medications.*.date_of_receipt' => 'required|date',
//            'medications.*.batch_number' => 'required|string|max:255',

//            'date_of_last_inventory',
//            'result',

            'total_volume' => 'required',
            'occupied_volume' => 'required',
            'occupied_volume_percentage' => 'required',
            'max_capacity' => 'required',

            'phone' => 'required',
            'email' => 'required',
            'work_time' => 'required',
            'head_name' => 'required',
            'head_position' => 'required',
            'head_contact' => 'required',

            // Условия хранения
            'temperature' => 'required',
            'humidity' => 'required',
            'light_protected' => 'required',

            // Особенности хранения
            'is_dangerous' => 'required',
            'refrigeration' => 'required',
            'freezing' => 'required',
            'flammable' => 'required',

            // Местоположение товаров
            'shelf_location' => 'required',
            'special_zone' => 'required',
            'label' => 'required',

            'supplier_id' => 'required',
            'transport' => 'required',
            'invoices' => 'required',
            'quality_certificates' => 'required',
            'acceptance_certificates' => 'required',
        ];
    }
}
