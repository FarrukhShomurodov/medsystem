<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'price' => 'required|numeric',
            'discount' => 'nullable|int',
            'currency' => 'nullable|string|max:255',
            'service_group_id' => 'required|int|exists:service_groups,id',
            'service_type_id' => 'required',
            'is_active' => 'required|bool',
            'is_appointment' => 'required|bool',
        ];
    }
}
