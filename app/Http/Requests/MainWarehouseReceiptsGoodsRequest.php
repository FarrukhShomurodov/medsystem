<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MainWarehouseReceiptsGoodsRequest extends FormRequest
{

    /**
     * @return array<string>
     */
    public function rules(): array
    {
        if ($this->medications) {
            $medications = array_filter($this->medications, function ($item) {
                return isset($item['id']) && $item['id'] !== 'ID' && !empty($item['quantity']);
            });

            $this->merge(['medications' => $medications]);
        }


        if ($this->supplies) {
            $supplies = array_filter($this->supplies, function ($item) {
                return isset($item['id']) && $item['id'] !== 'ID' && !empty($item['quantity']);
            });

            $this->merge(['supplies' => $supplies]);
        }


        if ($this->household) {
            $household = array_filter($this->household, function ($item) {
                return isset($item['id']) && $item['id'] !== 'ID' && !empty($item['quantity']);
            });

            $this->merge(['household' => $household]);
        }

        return [
            'medications' => 'nullable|array',
            'medications.*.id' => 'required|exists:medications,id',
            'medications.*.quantity' => 'required|integer|min:1',
            'medications.*.expiration_date' => 'required|date',
            'medications.*.date_of_receipt' => 'required|date',
            'medications.*.batch_number' => 'required|string|max:255',


            "medications.*.packaging_type" => 'required|string',
            "medications.*.quantity_per_package" => 'required|int',
            "medications.*.package_size" => 'required|int',
            "medications.*.retail_price" => 'required|int',
            "medications.*.wholesale_price" => 'required|int',
            "medications.*.supplier_id" => 'required|int',

            'supplies' => 'nullable|array',
            'supplies.*.id' => 'required|int',
            'supplies.*.quantity' => 'required|int',

            'household' => 'nullable|array',
            'household.*.id' => 'required|int',
            'household.*.quantity' => 'required|int',
        ];
    }
}
