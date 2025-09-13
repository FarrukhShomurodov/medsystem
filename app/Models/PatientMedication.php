<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PatientMedication extends Pivot
{
    protected $table = 'patient_medications';

//    protected $fillable = [
//        'status',
//        'date_of_end',
//    ];

    const STATUS = [
        'ordered',
        'sent_to_floor',
        'received_at_station',
        'completed',
        'returned',
        'cancelled',
    ];

    const STATUS_LABELS = [
        'ordered' => 'Заказан',
        'sent_to_floor' => 'Отправлен на этаж',
        'received_at_station' => 'Принят в посту',
        'completed' => 'Сделано',
        'returned' => 'Возвращено',
        'cancelled' => 'Отменено',
    ];

    const STATUS_ICONS = [
        'ordered' => 'fa-flask', // жёлтая колба
        'sent_to_floor' => 'fa-box', // жёлтая коробка
        'received_at_station' => 'fa-check', // жёлтая галочка
        'completed' => 'fa-check-circle', // зелёная галочка
        'returned' => 'fa-undo-alt', // коробка с обратной стрелкой
        'cancelled' => 'fa-circle-xmark', // коробка с обратной стрелкой
    ];

    const STATUS_COLORS = [
        'ordered' => 'text-warning',
        'sent_to_floor' => 'text-warning',
        'received_at_station' => 'text-warning',
        'completed' => 'text-success',
        'returned' => 'text-brown', // добавь свой цвет в CSS, если нет в Bootstrap
        'cancelled' => 'text-warning', // добавь свой цвет в CSS, если нет в Bootstrap
    ];

    const METHOD = [
        'iv_bolus_slow',
        'iv_drip',
        'oral',
        'intramuscular',
        'subcutaneous',
        'intradermal',
        'rectal',
        'nasal',
        'nasogastric',
        'sublingual',
        'auricular',
        'ophthalmic',
        'vaginal',
        'epidural',
        'transdermal',
        'intrathecal',
        'intraosseous',
        'endotracheal',
        'other',
    ];

    const METHOD_LABELS = [
        'iv_bolus_slow' => 'Внутривенно больюсно медленно',
        'iv_drip' => 'Внутривенно капельно',
        'oral' => 'Перорально',
        'intramuscular' => 'Внутримышечно',
        'subcutaneous' => 'Подкожно',
        'intradermal' => 'Внутрикожно',
        'rectal' => 'Ректально',
        'nasal' => 'Назально',
        'nasogastric' => 'Назогастрально',
        'sublingual' => 'Под язык',
        'auricular' => 'В ухо',
        'ophthalmic' => 'В глаз',
        'vaginal' => 'Вагинально',
        'epidural' => 'Эпидурально',
        'transdermal' => 'Трансдермально',
        'intrathecal' => 'Интратекально',
        'intraosseous' => 'Внутрикостно',
        'endotracheal' => 'Эндотрахеально',
        'other' => 'Другое',
    ];

    const ADMISSION_RULES = [
        'anytime',
        'before_meal',
        'during_meal',
        'after_meal',
        'before_sleep',
        'empty_stomach',
    ];

    const ADMISSION_RULE_LABELS = [
        'anytime' => 'Когда угодно',
        'before_meal' => 'Перед едой',
        'during_meal' => 'Во время еды',
        'after_meal' => 'После еды',
        'before_sleep' => 'Перед сном',
        'empty_stomach' => 'Натощак',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function compatibleMedications(): BelongsToMany
    {
        return $this->belongsToMany(
            Medication::class,
            'patient_medication_compatibility',
            'patient_medication_id',
            'compatible_medication_id',
        )->withPivot('dosage');
    }
}
