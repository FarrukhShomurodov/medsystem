<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicationLicenseAndCertification extends Model
{
    protected $fillable = [
        'medication_id',
        'sales_license',
        'quality_certificate',
        'clinical_trials',
        'registration_documents',
    ];
}
