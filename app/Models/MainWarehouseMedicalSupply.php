<?php

namespace App\Models;

use App\Services\Stock\RunsLowAtFixedLevel;
use App\Services\Stock\StockLot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The quantity of a medical supply in the main warehouse (one row per supply, no batches).
 *
 * @property int $quantity
 */
class MainWarehouseMedicalSupply extends Model implements StockLot
{
    use RunsLowAtFixedLevel;

    protected $fillable = [
        'main_warehouse_id',
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
     * @return BelongsTo<MainWarehouse, $this>
     */
    public function mainWarehouse(): BelongsTo
    {
        return $this->belongsTo(MainWarehouse::class);
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
