<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplyRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|int|exists:warehouses,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|gt:0',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Необходимо выбрать хотя бы один товар.',
            'items.*.quantity.min' => 'Количество должно быть минимум 1.',
        ];
    }
}
