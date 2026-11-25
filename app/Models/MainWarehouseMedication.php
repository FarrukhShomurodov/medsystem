<?php

namespace App\Models;

use App\Enums\PackagingType;
use App\Services\Stock\StockLot;
use Carbon\CarbonInterface;
use Database\Factories\MainWarehouseMedicationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * A received batch of a medication in the main warehouse: one row per receipt line, never merged.
 * `quantity` is what is left of the batch.
 *
 * @property int $quantity
 * @property Carbon $expiration_date
 * @property Carbon $date_of_receipt
 * @property PackagingType|null $packaging_type
 */
class MainWarehouseMedication extends Pivot implements StockLot
{
    /** @use HasFactory<MainWarehouseMedicationFactory> */
    use HasFactory;

    protected $table = 'main_warehouse_medications';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expiration_date' => 'date',
            'date_of_receipt' => 'date',
            'packaging_date' => 'date',
            'packaging_type' => PackagingType::class,
            'retail_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2',
            'markup' => 'decimal:2',
            'total_price' => 'decimal:2',
            'incoming_price_total' => 'decimal:2',
            'incoming_price_per_unit' => 'decimal:4',
            'incoming_sum' => 'decimal:2',
        ];
    }

    /**
     * Batches still in date. An expired batch is never dispensed or moved on, however early it
     * expires: it stays in the warehouse until it is written off.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function unexpired(Builder $query): void
    {
        $query->where('expiration_date', '>=', today()->toDateString());
    }

    /** Past its expiry date: the batch unexpired() leaves out, waiting to be written off. */
    public function isExpired(CarbonInterface $today): bool
    {
        return $this->expiration_date->toDateString() < $today->toDateString();
    }

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** @return BelongsTo<Medication, $this> */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /** @return BelongsTo<Medication, $this> */
    public function stockItem(): BelongsTo
    {
        return $this->medication();
    }
}
