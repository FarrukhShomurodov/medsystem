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

        // Медицинская информация
        'medical_history',
        'blood_type',
        'rh_factor',
        'special_instructions',

        'passport_scan',
        'insurance_policy_scan',
        'other_scans',
        'notes',
    ];

    public function allegories(): BelongsToMany
    {
        return $this->belongsToMany(Allegory::class, 'patient_allegories', 'patient_id', 'allegory_id');
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(MedicalDevice::class, 'patient_medical_devices', 'patient_id', 'medical_device_id');
    }

    public function diseases(): BelongsToMany
    {
        return $this->belongsToMany(ChronicDisease::class, 'patient_chronic_diseases', 'patient_id', 'chronic_disease_id');
    }


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

    // Examination Map
    public function examinationMaps(): HasMany
    {
        return $this->hasMany(ExaminationMap::class);
    }

    public function analyzes(): HasMany
    {
        return $this->hasMany(Analyze::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
