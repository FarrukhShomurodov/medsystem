<?php

namespace App\Models;

use App\Services\Stock\StockLot;
use Carbon\CarbonInterface;
use Database\Factories\WarehouseAccountingFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A batch of a medication in a department warehouse, as it came from the main warehouse.
 *
 * @property int $quantity
 */
class WarehouseAccounting extends Model implements StockLot
{
    /** @use HasFactory<WarehouseAccountingFactory> */
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'medication_id',
        'batch_number',
        'expiration_date',
        'date_of_receipt',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expiration_date' => 'date',
            'date_of_receipt' => 'date',
        ];
    }

    /**
     * Batches still in date: an expired batch is never dispensed or transferred.
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

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<Medication, $this> */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * @return HasOne<MedicationPackaging, $this>
     */
    public function packaging(): HasOne
    {
        return $this->hasOne(MedicationPackaging::class);
    }

    /**
     * @return HasOne<MedicationCostAndSupply, $this>
     */
    public function medicationCost(): HasOne
    {
        return $this->hasOne(MedicationCostAndSupply::class);
    }

    /** @return BelongsTo<Medication, $this> */
    public function stockItem(): BelongsTo
    {
        return $this->medication();
    }
}
