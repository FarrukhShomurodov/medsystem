<?php

namespace App\Models;

use App\Services\Stock\RunsLowAtFixedLevel;
use App\Services\Stock\StockLot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The quantity of a medical supply in a department warehouse.
 *
 * @property int $quantity
 */
class WarehouseMedicalSupply extends Model implements StockLot
{
    use RunsLowAtFixedLevel;

    protected $fillable = [
        'warehouse_id',
        'supply_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<MedicalSupply, $this>
     */
    public function supply(): BelongsTo
    {
        return $this->belongsTo(MedicalSupply::class, 'supply_id');
    }

    /** @return BelongsTo<MedicalSupply, $this> */
    public function stockItem(): BelongsTo
    {
        return $this->supply();
    }
}
