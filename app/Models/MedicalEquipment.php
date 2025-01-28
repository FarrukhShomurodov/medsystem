<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MedicalEquipment extends Model
{
    protected $table = 'medical_equipments';

    protected $fillable = [
        'name',
    ];


    public function analyzes(): BelongsToMany
    {
        return $this->belongsToMany(Analyze::class, 'analyze_to_methods_assignment', 'medical_equipment_id', 'analyze_id');

    }

}
