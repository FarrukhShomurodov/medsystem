<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExaminationMap extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'institution_id',
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
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function analyze(): HasMany
    {
        return $this->hasMany(Analyze::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(PatientService::class);
    }
}
