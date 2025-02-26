<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Medication extends Model
{
    protected $fillable = [
        'name',
        'release_form',
        'dosage',
        'manufacturer',
        'country_of_manufacture',
        'registration_number',
        'registration_date',
        'expiration_date',
    ];

    public function application(): HasOne
    {
        return $this->hasOne(MedicationApplication::class);
    }

    public function additionalData(): HasOne
    {
        return $this->hasOne(MedicationAdditionalData::class);
    }

    public function riskAndSafety(): HasOne
    {
        return $this->hasOne(MedicationRiskAndSafety::class);
    }

    public function composition(): HasOne
    {
        return $this->hasOne(MedicationComposition::class);
    }

    public function packaging(): HasOne
    {
        return $this->hasOne(MedicationPackaging::class);
    }

    public function storage(): HasOne
    {
        return $this->hasOne(MedicationStorage::class);
    }

    public function licenseAndCertification(): HasOne
    {
        return $this->hasOne(MedicationLicenseAndCertification::class);
    }

    public function medicationCost(): HasOne
    {
        return $this->hasOne(MedicationCostAndSupply::class);
    }
}
