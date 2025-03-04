<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_pharmacy_id' => 'required|exists:pharmacies,id',
            'medications' => 'required|array|min:1',
            'medications.*.medication_id' => 'required|exists:medications,id',
            'medications.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255'
        ];
    }
}
