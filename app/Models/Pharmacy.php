<?php

namespace App\Models;

use Database\Factories\PharmacyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Pharmacy extends Model
{
    /** @use HasFactory<PharmacyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'legal_address',
        'actual_address',
        'contact_phone',
        'working_hours',
    ];

    /**
     * @return BelongsTo<PharmacyType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PharmacyType::class, 'type_id', 'id');
    }

    /**
     * @return HasOne<PharmacyLicenseAndCertification, $this>
     */
    public function license(): HasOne
    {
        return $this->hasOne(PharmacyLicenseAndCertification::class);
    }

    /**
     * @return HasOne<PharmacySupportService, $this>
     */
    public function support(): HasOne
    {
        return $this->hasOne(PharmacySupportService::class);
    }

    /**
     * @return BelongsToMany<Medication, $this, Pivot>
     */
    public function medication(): BelongsToMany
    {
        return $this->belongsToMany(Medication::class, 'pharmacy_medications')->withPivot('quantity');
    }
}
