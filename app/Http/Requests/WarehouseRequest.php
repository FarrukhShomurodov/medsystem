<?php

namespace App\Http\Requests;

use App\Enums\PackagingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WarehouseRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'warehouse_type_id' => ['required', 'integer', 'exists:warehouse_types,id'],
            'address' => 'required',

            // packaging
            'medications.*.packaging_type' => ['required', Rule::enum(PackagingType::class)],
            'medications.*.quantity_per_package' => 'required|int',
            'medications.*.package_size' => 'required|int',
            'medications.*.packaging_date' => 'required|date',

            // price
            'medications.*.retail_price' => 'required|string|max:255',
            'medications.*.wholesale_price' => 'required|string|max:255',
            'medications.*.supplier_id' => 'required|string|max:255',

            'total_volume' => 'required',
            'occupied_volume' => 'required',
            'occupied_volume_percentage' => 'required',
            'max_capacity' => 'required',

            'phone' => 'required',
            'email' => 'required',
            'work_time' => 'required',
            'head_name' => 'required',
            'head_position' => 'required',
            'head_contact' => 'required',

            // Условия хранения
            'temperature' => 'required',
            'humidity' => 'required',
            'light_protected' => 'required',

            // Особенности хранения
            'is_dangerous' => 'required',
            'refrigeration' => 'required',
            'freezing' => 'required',
            'flammable' => 'required',

            // Местоположение товаров
            'shelf_location' => 'required',
            'special_zone' => 'required',
            'label' => 'required',
        ];
    }
}
