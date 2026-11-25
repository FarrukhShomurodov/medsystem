<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'discount' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'max:255'],
            'service_group_id' => ['required', 'integer', 'exists:service_groups,id'],
            'service_type_id' => ['required', 'integer', 'exists:service_types,id'],
            'is_active' => ['required', 'boolean'],
            'is_appointment' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'price' => 'стоимость',
            'service_group_id' => 'группа',
            'service_type_id' => 'тип',
        ];
    }
}
