<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use App\Services\Stock\StockLedger;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A department warehouse's order to the main warehouse: items of the kind the warehouse stores.
 */
class StoreSupplyRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'gt:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:'.StockLedger::MAX_LINE_QUANTITY],
        ];
    }

    /**
     * @return array{warehouse_id: int, items: list<array{id: int, quantity: int}>}
     */
    public function supply(): array
    {
        return [
            'warehouse_id' => $this->integer('warehouse_id'),
            'items' => array_values(array_map(fn (array $item): array => [
                'id' => (int) $item['id'],
                'quantity' => (int) $item['quantity'],
            ], $this->array('items'))),
        ];
    }

    /**
     * Each item must exist in the catalogue the warehouse type stores (medications, medical
     * supplies or household materials).
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $itemClass = Warehouse::query()->findOrFail($this->integer('warehouse_id'))->stockItemClass();

                if ($itemClass === null) {
                    $validator->errors()->add('warehouse_id', 'Склад этого типа не заказывает товары с главного склада.');

                    return;
                }

                $ids = collect((array) $this->input('items'))->map(fn (array $item): int => (int) $item['id']);
                $known = $itemClass::query()->whereKey($ids->unique()->all())->pluck('id');

                foreach ($ids as $index => $id) {
                    if ($known->doesntContain($id)) {
                        $validator->errors()->add("items.{$index}.id", 'Выбранный товар не относится к типу этого склада.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Необходимо выбрать хотя бы один товар.',
            'items.*.quantity.min' => 'Количество должно быть минимум 1.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'warehouse_id' => 'склад',
            'items.*.id' => 'товар',
            'items.*.quantity' => 'количество',
        ];
    }
}
