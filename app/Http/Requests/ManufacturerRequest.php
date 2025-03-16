<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManufacturerRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'country_of_manufacture' => 'required|string|max:255'
        ];
    }
}
