<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Patient extends Model
{

    public const BLOOD_TYPES = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

    /**
     * @var string[]
     */
    protected $fillable = [
        // Основные данные
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'gender',
        'citizenship',
        'passport_series',
        'passport_validity_period',

        // Контактная информация
        'phone',
        'additional_phone',
        'email',
        'region_id',
        'city_id',
        'residential_address_street',
        'residential_address_house',
        'residential_address_apartment',
        'residential_postcode',

        // Адрес регистрации
        'registered_region_id',
        'registered_city_id',
        'registered_address_street',
        'registered_address_house',
        'registered_address_apartment',
        'registered_postcode',

        // Страховка
        'insurance_policy_number',
        'insurance_company',
        'insurance_type',
        'insurance_expiry_date',

        // Данные экстренных контакто
        'emergency_contact_full_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'emergency_contact_address',
    ];

    // regions
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function registeredRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'registered_region_id');
    }

    public function registeredCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'registered_city_id');
    }

    // services
    public function examinationMaps(): HasMany
    {
        return $this->hasMany(ExaminationMap::class)
            ->with(['department', 'doctor'])
            ->orderByDesc('admission_date');
    }

    public function services(): HasMany
    {
        return $this->hasMany(PatientService::class)
            ->whereNull('examination_map_id')
            ->orderByDesc('created_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PatientAssignment::class)
            ->with(['service', 'staff'])
            ->orderByDesc('created_at');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponses::class, 'patient_id')
            ->whereNull('examination_map_id')
            ->whereNull('patient_service_id');
    }

    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class, 'patient_id')
            ->whereNull('examination_map_id');
    }

    // file
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function diagnosises(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class, 'patient_id', 'diagnosis_id')
            ->whereNull('examination_map_id');
    }

    public function documentResponse(): HasMany
    {
        return $this->hasMany(DocumentResponse::class, 'patient_id')
            ->whereNull('examination_map_id');
    }


    public function medications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medications',
            'patient_id',
            'medication_id'
        )->using(PatientMedication::class)
            ->withPivot('dosage', 'admission_period', 'id')
            ->whereNull('examination_map_id');
    }

    public function medicationsAll(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medications',
            'patient_id',
            'medication_id'
        )->using(PatientMedication::class);
    }

    public function vitalNormalParameters(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                VitalParameter::class,
                'patient_normal_vital_parameters',
                'patient_id',
                'vital_parameter_id'
            )
            ->using(PatientNormalVitalParameter::class)
            ->withPivot('normal', 'created_at');
    }


    public function procedures(): BelongsToMany
    {
        return $this->belongsToMany(
            Procedure::class,
            'patient_procedures',
            'patient_id',
            'procedure_id'
        )->using(PatientProcedure::class)
            ->whereNull('examination_map_id');
    }

//    public function analyzes(): HasMany
//    {
//        return $this->hasMany(Analyze::class);
//    }
//    public function allegories(): BelongsToMany
//    {
//        return $this->belongsToMany(Allegory::class, 'patient_allegories', 'patient_id', 'allegory_id');
//    }
//
//    public function devices(): BelongsToMany
//    {
//        return $this->belongsToMany(MedicalDevice::class, 'patient_medical_devices', 'patient_id', 'medical_device_id');
//    }
//
//    public function diseases(): BelongsToMany
//    {
//        return $this->belongsToMany(ChronicDisease::class, 'patient_chronic_diseases', 'patient_id', 'chronic_disease_id');
//    }
}
