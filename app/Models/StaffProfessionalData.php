<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfessionalData extends Model
{
    protected $fillable = [
        'staff_id',
        'position_id',
        'speciality_id',
        'department_id',

        'category',

        'education_name',
        'education_graduation_year',

        'certificate_name',
        'certificate_number',
        'certificate_issue_date',
        'certificate_validity_period',
    ];

    const CATEGORY = ['first', 'second'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
