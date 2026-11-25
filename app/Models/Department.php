<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'warehouse_id',
        'floor_id',
    ];

    /**
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo<Floor, $this>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * @return BelongsToMany<WardRoom, $this, Pivot>
     */
    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(WardRoom::class, 'department_rooms');
    }

    /**
     * @return HasMany<StaffProfessionalData, $this>
     */
    public function staffs(): HasMany
    {
        return $this->hasMany(StaffProfessionalData::class);
    }

    /**
     * The open stays of the department.
     *
     * @return HasMany<ExaminationMap, $this>
     */
    public function activeExaminationMaps(): HasMany
    {
        return $this->hasMany(ExaminationMap::class)->active();
    }
}
