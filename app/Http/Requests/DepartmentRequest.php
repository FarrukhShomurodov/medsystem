<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'floor' => 'nullable|int',
            'type_id' => 'required|exists:department_types,id',
            'warehouse_id' => 'required|exists:warehouses,id',

//            'total_beds' => 'required|integer|min:0',
//            'occupied_beds' => 'required|integer|min:0|lte:total_beds',

            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:20',

            'doctors_count' => 'integer|min:0',
            'nurses_count' => 'integer|min:0',
            'lab_technicians_count' => 'integer|min:0',

            'license_number' => 'required|string',
            'expiration_date' => 'required|date',
            'services' => 'required|array',
            'relations' => 'nullable|array',
            'equipments' => 'required|array',
            'rooms' => 'required|array',
        ];
    }
}
