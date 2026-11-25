<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Database\Factories\PatientProcedureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property ServiceStatus $status
 * @property Carbon|null $paid_at
 */
class PatientProcedure extends Pivot
{
    /** @use HasFactory<PatientProcedureFactory> */
    use HasFactory;

    protected $table = 'patient_procedures';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'status' => ServiceStatus::class,
            // The price list's price when the procedure was ordered: what the cash desk charges.
            'price' => 'decimal:2',
            // When the cash desk took the payment; NULL for an unpaid order, or one paid before it was recorded.
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Procedure, $this>
     */
    public function procedure(): BelongsTo
    {
        return $this->belongsTo(Procedure::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
