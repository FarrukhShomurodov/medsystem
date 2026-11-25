<?php

namespace App\Models;

use App\Enums\QualificationCategory;
use Database\Factories\StaffProfessionalDataFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property QualificationCategory|null $category
 */
class StaffProfessionalData extends Model
{
    /** @use HasFactory<StaffProfessionalDataFactory> */
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'category' => QualificationCategory::class,
        ];
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<StaffPosition, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class);
    }

    /**
     * @return BelongsTo<Specialty, $this>
     */
    public function speciality(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * @return BelongsTo<StaffPosition, $this>
     */
    public function additionPosition(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'addition_position_id');
    }
}
