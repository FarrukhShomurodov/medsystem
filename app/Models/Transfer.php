<?php

namespace App\Models;

use App\Enums\TransferStatus;
use Database\Factories\TransferFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Medications moved from a department warehouse to a pharmacy. The stock moves when the
 * transfer is completed, never before and never twice.
 *
 * @property TransferStatus $status
 */
class Transfer extends Model
{
    /** @use HasFactory<TransferFactory> */
    use HasFactory;

    protected $fillable = [
        'from_warehouse_id',
        'to_pharmacy_id',
        'transfer_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transfer_date' => 'date',
            'status' => TransferStatus::class,
        ];
    }

    /** @return BelongsTo<Warehouse, $this> */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /** @return BelongsTo<Pharmacy, $this> */
    public function toPharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'to_pharmacy_id');
    }

    /** @return HasMany<TransferMedication, $this> */
    public function medications(): HasMany
    {
        return $this->hasMany(TransferMedication::class);
    }
}
