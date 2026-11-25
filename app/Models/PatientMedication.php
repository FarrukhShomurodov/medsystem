<?php

namespace App\Models;

use App\Enums\AdministrationRoute;
use App\Enums\AdmissionRule;
use App\Enums\PatientMedicationStatus;
use Database\Factories\PatientMedicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * @property PatientMedicationStatus $status
 * @property AdministrationRoute $method
 * @property AdmissionRule $admission_rules
 * @property Carbon|null $paid_at
 */
class PatientMedication extends Pivot
{
    /** @use HasFactory<PatientMedicationFactory> */
    use HasFactory;

    protected $table = 'patient_medications';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'status' => PatientMedicationStatus::class,
            'method' => AdministrationRoute::class,
            'admission_rules' => AdmissionRule::class,
            'price' => 'decimal:2',
            // When the cash desk took the payment; NULL for an unpaid order, or one paid before it was recorded.
            'paid_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Medication, $this> */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * The inpatient stay the drug was prescribed in; null for an outpatient prescription.
     *
     * @return BelongsTo<ExaminationMap, $this>
     */
    public function examinationMap(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class);
    }

    /**
     * Units of the drug the prescription takes from stock and is billed for: the "dosage" field
     * holds the number of units (the price is the unit price times it).
     */
    public function units(): int
    {
        return max(1, (int) $this->dosage);
    }

    /**
     * @return BelongsToMany<Medication, $this, Pivot>
     */
    public function compatibleMedications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medication_compatibility',
            'patient_medication_id',
            'compatible_medication_id',
        )->withPivot('dosage');
    }
}
