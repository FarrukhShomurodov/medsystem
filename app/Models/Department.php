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

    public function service(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'department_services');
    }

    public function license(): HasOne
    {
        return $this->hasOne(DepartmentLicense::class);
    }
}
