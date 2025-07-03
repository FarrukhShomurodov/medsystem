<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    /**2
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            'equipment_type_id' => 'required|int|exists:equipment_types,id',
            'manufacturer_id' => 'required|int|exists:manufacturers,id',

            'serial_number' => 'required|string',

            'date_purchase' => 'required|date',
            'calibration_date' => 'required|date',
            'repair' => 'required|string',
            'term_date' => 'required|date',
            'term_notify_days' => 'required|int',
            'upcoming_service_at' => 'required|int',
            'location' => 'required|string',
            'staff_id' => 'required|int|exists:staff,id',

            'price' => 'required|numeric',

            'last_inspection' => 'nullable|date',
            'status' => ['nullable', Rule::in(Equipment::STATUS)],
        ];
    }
}
