<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExaminationMap extends Model
{
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

    public function services(): HasMany
    {
        return $this->hasMany(PatientService::class)->with('service');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponses::class, 'examination_map_id');
    }

    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medications',
            'examination_map_id',
            'medication_id'
        )->using(PatientMedication::class)
            ->withPivot('dosage', 'admission_period', 'id');
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class);
    }

    // Belongs to
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function documentResponse(): HasMany
    {
        return $this->hasMany(DocumentResponse::class, 'examination_map_id');
    }

    public function diagnosises(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class, 'examination_map_id');
    }

    public function vitalParameters(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                VitalParameter::class,
                'examination_map_vital_parameters',
                'examination_map_id',
                'vital_parameter_id'
            )
            ->using(ExaminationMapVitalParameter::class)
            ->withPivot('value', 'created_at');
    }

    public function procedures(): BelongsToMany
    {
        return $this->belongsToMany(
            Procedure::class,
            'patient_procedures',
            'patient_id',
            'procedure_id'
        )->using(PatientProcedure::class);
    }

//    public function analyze(): HasMany
//    {
//        return $this->hasMany(Analyze::class);
//    }
}
