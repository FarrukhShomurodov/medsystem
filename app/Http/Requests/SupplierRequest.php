<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'contract_terms' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'phone' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'delivery_schedule' => 'required|string|max:255',
            'manufacturers' => 'required|array',
            'manufacturers.*' => 'required|int|exists:manufacturers,id',
        ];
    }
}
