<?php

namespace App\Models;

use Database\Factories\ExaminationMapFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ExaminationMap extends Model
{
    /** @use HasFactory<ExaminationMapFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'staff_id',
        'department_id',
        'admission_date',
        'discharge_date',
        'admission_diagnosis_code',
        'admission_diagnosis_description',
        'discharge_diagnosis_code',
        'discharge_diagnosis_description',
        'examination_purpose',
        'complaints',
        'general_anamnesises',
        'disease_history',
        'is_active',
        'is_outpatient',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_outpatient' => 'boolean',
        ];
    }

    /**
     * The open stays: the one definition of an active map. A patient has at most one (partial
     * unique index examination_maps_one_active_per_patient).
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return HasMany<PatientService, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(PatientService::class)->with('service');
    }

    /** @return HasMany<SurveyResponse, $this> */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'examination_map_id');
    }

    /** @return BelongsToMany<Medication, $this, PatientMedication> */
    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medications',
            'examination_map_id',
            'medication_id',
        )->using(PatientMedication::class)
            ->withPivot('dosage', 'admission_period', 'id');
    }

    /** @return HasMany<Reception, $this> */
    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }

    /** @return BelongsTo<Staff, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return HasMany<DocumentResponse, $this> */
    public function documentResponses(): HasMany
    {
        return $this->hasMany(DocumentResponse::class, 'examination_map_id');
    }

    /** @return HasMany<PatientDiagnosis, $this> */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class, 'examination_map_id');
    }

    /** @return BelongsToMany<VitalParameter, $this, ExaminationMapVitalParameter> */
    public function vitalParameters(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                VitalParameter::class,
                'examination_map_vital_parameters',
                'examination_map_id',
                'vital_parameter_id',
            )
            ->using(ExaminationMapVitalParameter::class)
            ->withPivot('value', 'created_at');
    }

    /** @return BelongsToMany<Procedure, $this, PatientProcedure> */
    public function procedures(): BelongsToMany
    {
        return $this->belongsToMany(
            Procedure::class,
            'patient_procedures',
            'examination_map_id',
            'procedure_id',
        )->using(PatientProcedure::class);
    }

    /**
     * Procedure orders of this stay as records of their own (with status and date).
     *
     * @return HasMany<PatientProcedure, $this>
     */
    public function patientProcedures(): HasMany
    {
        return $this->hasMany(PatientProcedure::class);
    }

    /**
     * Prescriptions of this stay as records of their own (with status and price).
     *
     * @return HasMany<PatientMedication, $this>
     */
    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class);
    }

    /**
     * Calendar days from admission to discharge, or to today while the patient is still in.
     */
    public function daysInHospital(?Carbon $today = null): int
    {
        $end = $this->discharge_date ? Carbon::parse($this->discharge_date) : ($today ?? Carbon::today());

        return max(0, (int) Carbon::parse($this->admission_date)->startOfDay()->diffInDays($end->copy()->startOfDay()));
    }
}
