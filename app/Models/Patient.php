<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @property Gender $gender
 * @property-read string $full_name
 */
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    /**
     * @var list<string>
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

        // новое поле
        'referral_source',

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

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
        ];
    }

    /**
     * "Фамилия Имя Отчество", the one order a patient's name is printed in across the dashboard.
     * A query that selects columns selects all three names.
     *
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->last_name} {$this->first_name} {$this->middle_name}"));
    }

    /** @return BelongsTo<Region, $this> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsTo<Region, $this> */
    public function registeredRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'registered_region_id');
    }

    /** @return BelongsTo<City, $this> */
    public function registeredCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'registered_city_id');
    }

    /** @return HasMany<ExaminationMap, $this> */
    public function examinationMaps(): HasMany
    {
        return $this->hasMany(ExaminationMap::class)
            ->with(['department', 'doctor'])
            ->orderByDesc('admission_date');
    }

    /**
     * The current hospital stay. A patient has at most one active map (partial unique index).
     *
     * @return HasOne<ExaminationMap, $this>
     */
    public function activeExaminationMap(): HasOne
    {
        return $this->hasOne(ExaminationMap::class)->active();
    }

    /**
     * The most recent stay, active or not: the patient lists show its department and doctor.
     *
     * @return HasOne<ExaminationMap, $this>
     */
    public function latestExaminationMap(): HasOne
    {
        return $this->hasOne(ExaminationMap::class)->ofMany(['admission_date' => 'max', 'id' => 'max']);
    }

    /**
     * Every service ordered for the patient, during a stay or outside one (examination_map_id).
     *
     * @return HasMany<PatientService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(PatientService::class)->orderByDesc('created_at');
    }

    /** @return HasMany<PatientAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PatientAssignment::class)
            ->with(['service', 'staff'])
            ->orderByDesc('created_at');
    }

    /**
     * The medical document sheets filled in for the patient on their own, during a stay or outside
     * one; the sheets of an ordered service belong to that service (PatientService::surveyResponses()).
     *
     * @return HasMany<SurveyResponse, $this>
     */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class, 'patient_id')->whereNull('patient_service_id');
    }

    /**
     * Every appointment of the patient, during a stay or outside one.
     *
     * @return HasMany<Reception, $this>
     */
    public function receptions(): HasMany
    {
        return $this->hasMany(Reception::class, 'patient_id');
    }

    /** @return MorphMany<File, $this> */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Every diagnosis made for the patient, during a stay or outside one.
     *
     * @return HasMany<PatientDiagnosis, $this>
     */
    public function diagnoses(): HasMany
    {
        return $this->hasMany(PatientDiagnosis::class, 'patient_id');
    }

    /**
     * Every medical document filled in for the patient, during a stay or outside one.
     *
     * @return HasMany<DocumentResponse, $this>
     */
    public function documentResponses(): HasMany
    {
        return $this->hasMany(DocumentResponse::class, 'patient_id');
    }

    /**
     * The latest initial nursing assessment not attached to a stay yet: the one the next stay of the
     * patient takes over (ExaminationMapService::store()).
     */
    public function pendingInitialAssessment(): ?DocumentResponse
    {
        return $this->documentResponses()->unattachedInitialAssessment()->latest()->first();
    }

    /**
     * Every medication prescribed to the patient, inpatient stays included; the pivot is the
     * prescription (PatientMedication).
     *
     * @return BelongsToMany<Medication, $this, PatientMedication>
     */
    public function prescribedMedications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medications',
            'patient_id',
            'medication_id',
        )->using(PatientMedication::class);
    }

    /** @return BelongsToMany<VitalParameter, $this, PatientNormalVitalParameter> */
    public function vitalNormalParameters(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                VitalParameter::class,
                'patient_normal_vital_parameters',
                'patient_id',
                'vital_parameter_id',
            )
            ->using(PatientNormalVitalParameter::class)
            ->withPivot('normal', 'created_at');
    }

    /**
     * Procedure orders as records of their own (with status and date), inpatient ones included.
     *
     * @return HasMany<PatientProcedure, $this>
     */
    public function patientProcedures(): HasMany
    {
        return $this->hasMany(PatientProcedure::class);
    }

    /**
     * Prescriptions as records of their own (with status and price), inpatient ones included.
     *
     * @return HasMany<PatientMedication, $this>
     */
    public function patientMedications(): HasMany
    {
        return $this->hasMany(PatientMedication::class);
    }
}
