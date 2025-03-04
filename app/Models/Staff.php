<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Staff extends Model
{
    protected $fillable = [
        'name',
        'last_name',
        'middle_name',
        'date_birthday',
        'gender',
    ];

    const GENDER = ['male', 'female'];

    public function contact(): HasOne
    {
        return $this->hasOne(StaffContact::class);
    }

    public function document(): HasOne
    {
        return $this->hasOne(StaffDocument::class);
    }


    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'staff_service_assignment', 'staff_id', 'staff_service_id');
    }

    public function professionalData(): HasOne
    {
        return $this->hasOne(StaffProfessionalData::class);
    }
}
