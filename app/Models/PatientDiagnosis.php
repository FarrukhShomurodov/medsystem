<?php

namespace App\Models;

use Database\Factories\PatientDiagnosisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDiagnosis extends Model
{
    /** @use HasFactory<PatientDiagnosisFactory> */
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'examination_map_id',
        'diagnosis_id',
    ];

    /**
     * @return BelongsTo<Diagnosis, $this>
     */
    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }
}
