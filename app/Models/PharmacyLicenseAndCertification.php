<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyLicenseAndCertification extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'quality_certificate_number',
        'compliance_standards',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
