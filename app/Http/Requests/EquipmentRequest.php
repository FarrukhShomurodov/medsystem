<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',

            'equipment_type_id' => 'required|int|exists:equipment_types,id',
            'manufacturer_id' => 'required|int|exists:manufacturers,id',

            'serial_number' => 'required|string|max:255',

            'date_purchase' => 'required|date',
            'calibration_date' => 'required|date',
            'repair' => 'required|string|max:255',
            'term_date' => 'required|date',
            'term_notify_days' => 'required|int|min:0|max:36500',
            'upcoming_service_at' => 'required|int|min:0|max:36500',
            'location' => 'required|string|max:255',
            'staff_id' => 'required|int|exists:staff,id',

            'price' => 'required|numeric|min:0|max:999999999999.99',

            'last_inspection' => 'nullable|date',
            'status' => ['nullable', Rule::enum(EquipmentStatus::class)],
        ];
    }
}
