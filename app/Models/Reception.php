<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reception extends Model
{
    const STATUS = ['new', 'done', 'paid', 'cancelled'];

    protected $fillable = [
        'examination_map_id',
        'patient_id',
        'staff_id',
        'start_time',
        'end_time',
        'date',
        'comment',
        'status',
    ];


    public function statusTranslate($status)
    {
        $statusRu = [
            'new' => 'Новое',
            'paid' => 'Оплачено',
            'cancelled' => 'Отмена',
            'done' => 'Выполнено'
        ];
        return $statusRu[$status];
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class, 'examination_map_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
