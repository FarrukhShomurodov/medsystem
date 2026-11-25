<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ServicePlace;
use App\Enums\ServiceStatus;
use Database\Factories\PatientServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property ServiceStatus $status
 * @property ServicePlace|null $place
 * @property array<string, mixed>|null $payment
 * @property Carbon|null $paid_at
 */
class PatientService extends Model
{
    /** @use HasFactory<PatientServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'patient_id',
        'examination_map_id',
        'service_date',
        'service_time',
        'place',
        'result',
        'status',
        'payment',
        'insurance_policy',
        'price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceStatus::class,
            'place' => ServicePlace::class,
            // The price list's price when the service was ordered (in its kept currency): what the cash desk charges.
            'price' => 'decimal:2',
            // When the cash desk took the payment; NULL for an unpaid order, or one paid before it was recorded.
            'paid_at' => 'datetime',
            'payment' => 'array',
            'insurance_policy' => 'array',
        ];
    }

    public function isPaid(): bool
    {
        return $this->status->isPaid();
    }

    /** The payment status noted on the order form (payment.status), if one was picked. */
    public function paymentStatus(): ?PaymentStatus
    {
        return PaymentStatus::tryFrom((string) ($this->payment['status'] ?? ''));
    }

    /** The payment method noted on the order form (payment.method), if one was picked. */
    public function paymentMethod(): ?PaymentMethod
    {
        return PaymentMethod::tryFrom((string) ($this->payment['method'] ?? ''));
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The staff member performing the service; patient_services has no doctor column of its own.
     *
     * @return HasOneThrough<Staff, PatientServiceStaff, $this>
     */
    public function doctor(): HasOneThrough
    {
        return $this->hasOneThrough(
            Staff::class,
            PatientServiceStaff::class,
            'patient_service_id',
            'id',
            'id',
            'service_staff_id',
        );
    }

    /**
     * @return BelongsToMany<Equipment, $this, Pivot>
     */
    public function equipments(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'patient_service_to_equipments_assignment', 'patient_service_id', 'service_equipment_id');
    }

    /**
     * @return BelongsToMany<ServiceMethod, $this, Pivot>
     */
    public function methods(): BelongsToMany
    {
        return $this->belongsToMany(ServiceMethod::class, 'patient_service_to_methods_assignments', 'patient_service_id', 'service_methods_id');
    }

    /**
     * @return BelongsToMany<Staff, $this, Pivot>
     */
    public function staffs(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'patient_service_to_staffs_assignment', 'patient_service_id', 'service_staff_id');
    }

    /**
     * @return HasMany<SurveyResponse, $this>
     */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
