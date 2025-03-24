<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationGroup extends Model
{
    protected $fillable = ['name'];

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }
}
