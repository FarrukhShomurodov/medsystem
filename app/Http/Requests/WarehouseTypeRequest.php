<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseTypeRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:300',
        ];
    }
}
