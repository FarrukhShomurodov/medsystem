<?php

namespace App\Models;

use App\Enums\ReceptionStatus;
use Database\Factories\ReceptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property ReceptionStatus $status
 * @property Carbon|null $paid_at
 */
class Reception extends Model
{
    /** @use HasFactory<ReceptionFactory> */
    use HasFactory;

    protected $fillable = [
        'examination_map_id',
        'patient_id',
        'staff_id',
        'service_id',
        'start_time',
        'end_time',
        'date',
        'comment',
        'status',
        'price',
        'currency',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReceptionStatus::class,
            // The price list's price of the service when the appointment was booked (in its kept currency): what the cash desk charges.
            'price' => 'decimal:2',
            // When the cash desk took the payment; NULL for an unpaid order, or one paid before it was recorded.
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ExaminationMap, $this>
     */
    public function map(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class, 'examination_map_id');
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
