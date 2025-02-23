<?php

namespace App\Http\Requests;

use App\Models\DepartmentEquipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentEquipmentRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_inspection' => 'required|date',
            'status' => ['required', Rule::in(DepartmentEquipment::STATUS)],
        ];
    }
}
