<?php

namespace App\Http\Requests;

use App\Models\Equipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_inspection' => 'required|date',
            'status' => ['required', Rule::in(Equipment::STATUS)],
        ];
    }
}
