<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfessionalData extends Model
{
    protected $fillable = [
        'staff_id',
        'position_id',
        'addition_position_id',
        'speciality_id',
        'department_id',

        'category',

        'education_name',
        'education_graduation_year',

        'certificate_name',
        'certificate_number',
        'certificate_issue_date',
        'certificate_validity_period',

        'admission_period',
    ];

    const CATEGORY = ['first', 'second'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class);
    }

    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function additionPosition(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class,'addition_position_id');
    }
}
