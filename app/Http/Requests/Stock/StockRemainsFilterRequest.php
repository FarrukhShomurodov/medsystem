<?php

namespace App\Http\Requests\Stock;

use App\Services\Stock\StockSection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The filters of the remains pages and of the receipts page: the section (?type=) and, on the
 * department remains page, the warehouse. Authorization is the routes'.
 */
class StockRemainsFilterRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::enum(StockSection::class)],
            'warehouse_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'тип',
            'warehouse_id' => 'склад',
        ];
    }

    /**
     * A refused filter goes back to the same page unfiltered (its URL without the query), never to
     * the previous URL: that is the filtered page itself once the filter was submitted twice.
     */
    protected function getRedirectUrl(): string
    {
        return $this->url();
    }

    public function section(): ?StockSection
    {
        return $this->enum('type', StockSection::class);
    }

    public function warehouseId(): ?int
    {
        return $this->filled('warehouse_id') ? $this->integer('warehouse_id') : null;
    }
}
