<?php

namespace App\Http\Requests;

use App\Models\WarehouseType;
use App\Services\Stock\StockLedger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * A transfer from a department warehouse to a pharmacy (create and edit forms).
 */
class TransferRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Exists>>
     */
    public function rules(): array
    {
        return [
            // Only a medication warehouse holds the batches a transfer takes from.
            'from_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')->where('warehouse_type_id', WarehouseType::MEDICATIONS)],
            'to_pharmacy_id' => ['required', 'integer', 'exists:pharmacies,id'],
            'medications' => ['required', 'array', 'min:1'],
            'medications.*.medication_id' => ['required', 'integer', 'distinct', 'exists:medications,id'],
            'medications.*.quantity' => ['required', 'integer', 'min:1', 'max:'.StockLedger::MAX_LINE_QUANTITY],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{from_warehouse_id: int, to_pharmacy_id: int, notes: string|null, medications: list<array{medication_id: int, quantity: int}>}
     */
    public function transfer(): array
    {
        return [
            'from_warehouse_id' => $this->integer('from_warehouse_id'),
            'to_pharmacy_id' => $this->integer('to_pharmacy_id'),
            'notes' => $this->filled('notes') ? $this->string('notes')->value() : null,
            'medications' => array_values(array_map(fn (array $line): array => [
                'medication_id' => (int) $line['medication_id'],
                'quantity' => (int) $line['quantity'],
            ], $this->array('medications'))),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from_warehouse_id.exists' => 'Переводить в аптеку можно только со склада медикаментов.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'from_warehouse_id' => 'склад',
            'to_pharmacy_id' => 'аптека',
            'medications' => 'препараты',
            'medications.*.medication_id' => 'препарат',
            'medications.*.quantity' => 'количество',
            'notes' => 'примечание',
        ];
    }
}
