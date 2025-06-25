<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExaminationMapVitalParameter extends Pivot
{
    protected $table = 'examination_map_vital_parameters';

    protected $fillable = [
        'examination_map_id',
        'vital_parameter_id',
        'value',
    ];

    public function vitalParameter(): BelongsTo
    {
        return $this->belongsTo(VitalParameter::class);
    }

    public function examinationMap(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class);
    }
}
