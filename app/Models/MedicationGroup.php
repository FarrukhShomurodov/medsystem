<?php

namespace App\Models;

use Database\Factories\MedicationGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicationGroup extends Model
{
    /** @use HasFactory<MedicationGroupFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return HasMany<Medication, $this>
     */
    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class, 'group_id');
    }
}
