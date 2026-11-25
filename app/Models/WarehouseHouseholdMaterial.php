<?php

namespace App\Models;

use App\Services\Stock\RunsLowAtFixedLevel;
use App\Services\Stock\StockLot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The quantity of a household material in a department warehouse.
 *
 * @property int $quantity
 */
class WarehouseHouseholdMaterial extends Model implements StockLot
{
    use RunsLowAtFixedLevel;

    protected $fillable = [
        'warehouse_id',
        'household_material_id',
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
     * @return BelongsTo<HouseholdMaterial, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(HouseholdMaterial::class, 'household_material_id');
    }

    /** @return BelongsTo<HouseholdMaterial, $this> */
    public function stockItem(): BelongsTo
    {
        return $this->material();
    }
}
