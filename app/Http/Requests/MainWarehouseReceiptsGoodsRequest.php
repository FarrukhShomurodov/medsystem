<?php

namespace App\Http\Requests;

use App\Enums\PackagingType;
use App\Services\Stock\StockLedger;
use App\Services\Stock\StockSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A receipt of goods into the main warehouse. Each medication line is a batch of its own.
 */
class MainWarehouseReceiptsGoodsRequest extends FormRequest
{
    /**
     * The form renders a line for every item picked in its selects; lines left without a
     * quantity were not received and are dropped before validation.
     */
    protected function prepareForValidation(): void
    {
        foreach (StockSection::values() as $section) {
            if (! is_array($this->input($section))) {
                continue;
            }

            $this->merge([$section => array_values(array_filter(
                $this->input($section),
                fn (mixed $line): bool => is_array($line) && filled($line['id'] ?? null) && filled($line['quantity'] ?? null),
            ))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $quantity = ['required', 'integer', 'min:1', 'max:'.StockLedger::MAX_LINE_QUANTITY];

        return [
            'medications' => ['nullable', 'array', 'required_without_all:supplies,household'],
            'medications.*.id' => ['required', 'integer', 'exists:medications,id'],
            'medications.*.quantity' => $quantity,
            'medications.*.expiration_date' => ['required', 'date', 'after:today'],
            'medications.*.date_of_receipt' => ['required', 'date', 'before_or_equal:today'],
            'medications.*.batch_number' => ['required', 'string', 'max:255'],

            'medications.*.packaging_type' => ['nullable', Rule::enum(PackagingType::class)],
            'medications.*.quantity_per_package' => ['nullable', 'integer', 'min:1', 'max:'.StockLedger::MAX_LINE_QUANTITY],
            'medications.*.package_size' => ['nullable', 'integer', 'min:1', 'max:'.StockLedger::MAX_LINE_QUANTITY],
            'medications.*.retail_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'medications.*.wholesale_price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'medications.*.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],

            'medications.*.markup' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'medications.*.total_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'medications.*.incoming_price_total' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'medications.*.incoming_price_per_unit' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'medications.*.incoming_sum' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],

            'supplies' => ['nullable', 'array'],
            'supplies.*.id' => ['required', 'integer', 'exists:medical_supplies,id'],
            'supplies.*.quantity' => $quantity,

            'household' => ['nullable', 'array'],
            'household.*.id' => ['required', 'integer', 'exists:household_materials,id'],
            'household.*.quantity' => $quantity,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'medications.required_without_all' => 'Добавьте в приход хотя бы один товар с количеством.',
            'medications.*.expiration_date.after' => 'Партия с истёкшим сроком годности не принимается на склад.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'medications.*.id' => 'медикамент',
            'medications.*.quantity' => 'количество',
            'medications.*.expiration_date' => 'срок годности',
            'medications.*.date_of_receipt' => 'дата получения',
            'medications.*.batch_number' => 'номер партии',
            'medications.*.packaging_type' => 'тип упаковки',
            'medications.*.quantity_per_package' => 'количество в упаковке',
            'medications.*.package_size' => 'размер упаковки',
            'medications.*.retail_price' => 'розничная цена',
            'medications.*.wholesale_price' => 'закупочная цена',
            'medications.*.supplier_id' => 'поставщик',
            'medications.*.markup' => 'наценка',
            'medications.*.total_price' => 'сумма',
            'medications.*.incoming_price_total' => 'входная цена (всего)',
            'medications.*.incoming_price_per_unit' => 'входная цена за единицу',
            'medications.*.incoming_sum' => 'входная сумма',
            'supplies.*.id' => 'расходный материал',
            'supplies.*.quantity' => 'количество',
            'household.*.id' => 'хозяйственный материал',
            'household.*.quantity' => 'количество',
        ];
    }
}
