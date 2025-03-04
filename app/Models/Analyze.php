<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analyze extends Model
{
    protected $fillable = [
        'patient_id',
        'examination_map_id',
        'laboratory_id',
        'staff_id',
        'analysis_time',
        'expected_result_date',
        'medical_staff',
        'target',
        'status',
        'notes'
    ];


    public const MEDICAL_STAFF = ['assist', 'doctor'];
    public const STATUS = ['assigned', 'completed', 'received'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function examinationMap(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class);
    }

    public function laboratory(): BelongsTo
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function equipments(): belongsToMany
    {
        return $this->belongsToMany(MedicalEquipment::class, 'analyze_to_equipments_assignment', 'analyze_id', 'medical_equipment_id');
    }


    public function methods(): belongsToMany
    {
        return $this->belongsToMany(AnalyzeMethod::class, 'analyze_to_methods_assignment', 'analyze_id', 'analyze_method_id');
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(AnalyzeType::class, 'analyze_to_type_assignment', 'analyze_id', 'type_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(AnalyzeResult::class);
    }
}
