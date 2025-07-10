<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HouseholdMaterialRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'batch_number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'entry_price_total' => 'required|numeric',
            'entry_price_unit' => 'required|numeric',
        ];
    }
}
