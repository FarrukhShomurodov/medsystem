<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pharmacy extends Model
{
    protected $fillable = [
        'name',
        'type_id',
        'legal_address',
        'actual_address',
        'contact_phone',
        'working_hours'
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PharmacyType::class, 'type_id', 'id');
    }

    public function license(): HasOne
    {
        return $this->hasOne(PharmacyLicenseAndCertification::class);
    }

    public function support(): HasOne
    {
        return $this->hasOne(PharmacySupportService::class);
    }

    public function medication(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class, 'pharmacy_medications')->withPivot('quantity');
    }
}
