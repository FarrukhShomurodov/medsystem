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
            // main info
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'code' => 'nullable|string|max:255',
            'price' => 'required|int',
            'price_vat' => 'required|int',
            'discount' => 'nullable|int',
            'currency' => 'nullable|string|max:255',
//            'duration' => 'nullable|string|max:255',
            'service_category_id' => 'required',
            'is_active' => 'required|bool',
        ];
    }
}
