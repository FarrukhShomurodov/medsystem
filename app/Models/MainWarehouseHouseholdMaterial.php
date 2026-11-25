<?php

namespace App\Models;

use App\Services\Stock\RunsLowAtFixedLevel;
use App\Services\Stock\StockLot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The quantity of a household material in the main warehouse (one row per material, no batches).
 *
 * @property int $quantity
 */
class MainWarehouseHouseholdMaterial extends Model implements StockLot
{
    use RunsLowAtFixedLevel;

    protected $fillable = [
        'main_warehouse_id',
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
     * @return BelongsTo<MainWarehouse, $this>
     */
    public function mainWarehouse(): BelongsTo
    {
        return $this->belongsTo(MainWarehouse::class);
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
