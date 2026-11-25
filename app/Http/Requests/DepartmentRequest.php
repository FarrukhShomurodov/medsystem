<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'floor_id' => ['nullable', 'integer', 'exists:floors,id'],
            'rooms' => ['required', 'array', 'min:1'],
            'rooms.*' => ['required', 'integer', 'distinct', 'exists:ward_rooms,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'warehouse_id' => 'склад',
            'floor_id' => 'этаж',
            'rooms' => 'палаты',
            'rooms.*' => 'палата',
        ];
    }
}
