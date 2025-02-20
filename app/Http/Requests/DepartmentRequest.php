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
            'type_id' => 'required|exists:department_types,id',
            'total_beds' => 'required|integer|min:0',
            'occupied_beds' => 'required|integer|min:0|lte:total_beds',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'fax' => 'nullable|string|max:20',
            'head_name' => 'nullable|string|max:255',
            'head_position' => 'nullable|string|max:255',
            'head_contact' => 'nullable|string|max:255',
            'doctors_count' => 'integer|min:0',
            'nurses_count' => 'integer|min:0',
            'lab_technicians_count' => 'integer|min:0',
            'last_inspection' => 'nullable|date',
            'status' => 'required|in:active,inactive,pending',
            'license_number' => 'required|string',
            'expiration_date' => 'required|date|after:today',
            'services' => 'required|array',
        ];
    }
}
