<?php

namespace App\Models;

use App\Enums\StockMovementReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One change of a stock quantity, written by StockLedger. The ledger is append-only.
 *
 * @property int $quantity
 * @property StockMovementReason $reason
 * @property string $stock_type
 * @property int $stock_id
 */
class StockMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'stock_type',
        'stock_id',
        'item_type',
        'item_id',
        'quantity',
        'reason',
        'source_type',
        'source_id',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reason' => StockMovementReason::class,
        ];
    }

    /**
     * The stock row whose quantity changed; gone when the row was written off.
     *
     * @return MorphTo<Model, $this>
     */
    public function stock(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return MorphTo<Model, $this> */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The document behind the change: a supply request, a transfer or a prescription.
     *
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
