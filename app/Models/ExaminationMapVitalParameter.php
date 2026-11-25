<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExaminationMapVitalParameter extends Pivot
{
    protected $table = 'examination_map_vital_parameters';

    /** The pivot table has its own surrogate key. */
    public $incrementing = true;

    protected $fillable = [
        'examination_map_id',
        'vital_parameter_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:1',
        ];
    }

    /** @return BelongsTo<VitalParameter, $this> */
    public function vitalParameter(): BelongsTo
    {
        return $this->belongsTo(VitalParameter::class);
    }

    /** @return BelongsTo<ExaminationMap, $this> */
    public function examinationMap(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class);
    }
}
