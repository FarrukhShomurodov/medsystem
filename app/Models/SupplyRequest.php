<?php

namespace App\Models;

use App\Enums\SupplyRequestStatus;
use Database\Factories\SupplyRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A department warehouse's order to the main warehouse. The main warehouse decides it once:
 * approving moves the stock, rejecting (with an optional reason) moves nothing.
 *
 * @property SupplyRequestStatus $status
 * @property string|null $rejection_reason
 */
class SupplyRequest extends Model
{
    /** @use HasFactory<SupplyRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'main_warehouse_id',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => SupplyRequestStatus::class,
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** @return BelongsTo<MainWarehouse, $this> */
    public function mainWarehouse(): BelongsTo
    {
        return $this->belongsTo(MainWarehouse::class);
    }

    /** @return HasMany<SupplyRequestItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(SupplyRequestItem::class, 'request_id');
    }
}
