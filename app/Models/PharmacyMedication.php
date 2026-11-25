<?php

namespace App\Models;

use App\Services\Stock\StockLot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The quantity of a medication in a pharmacy, filled by completed transfers.
 *
 * @property int $quantity
 */
class PharmacyMedication extends Model implements StockLot
{
    protected $fillable = [
        'pharmacy_id',
        'medication_id',
        'quantity',
        'discount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'discount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Pharmacy, $this>
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * @return BelongsTo<Medication, $this>
     */
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
