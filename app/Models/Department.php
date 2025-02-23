<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Department extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type_id',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(DepartmentType::class);
    }

    public function contact(): HasOne
    {
        return $this->hasOne(DepartmentContact::class);
    }

    public function bed(): HasOne
    {
        return $this->hasOne(DepartmentBed::class);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(DepartmentEmploye::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(DepartmentService::class, 'department_service_assignment', 'department_id', 'department_service_id');
    }

    public function relations(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_relations', 'department_id', 'relation_department_id');
    }

    public function equipments(): BelongsToMany
    {
        return $this->belongsToMany(DepartmentService::class, 'department_equipment_assignment', 'department_id', 'department_equipment_id');
    }

    public function license(): HasOne
    {
        return $this->hasOne(DepartmentLicense::class);
    }
}
