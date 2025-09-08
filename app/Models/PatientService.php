<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientService extends Model
{
    const PLACE = ['institution', 'cabinet'];
    const STATUS = ['assigned', 'in_process', 'completed', 'cancelled', 'confirmed', 'paid'];
    const PAYMENT_STATUS = ['paid', 'unpaid', 'partially_paid'];
    const PAYMENT_METHOD = ['cash', 'card', 'insurance_company'];

    protected $fillable = [
        'service_id',
        'patient_id',
        'examination_map_id',
        'service_date',
        'service_time',
        'place',
        'result',
        'status',
        'payment',
        'insurance_policy'
    ];

    protected $casts = [
        'insurance_policy' => 'array',
        'payment' => 'array',
    ];

    public static function getStatusTranslate($status)
    {
        $statusRu = [
            'assigned' => 'Назначена',
            'in_process' => 'В процессе',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
            'confirmed' => 'Подтверждено',
            'paid' => 'Оплачено',
        ];

        return $statusRu[$status];
    }

    public static function getPaymentStatusTranslate($status)
    {
        $statusRu = [
            'paid' => 'Оплачено',
            'unpaid' => 'Не оплачено',
            'partially_paid' => 'Частично оплачено',
        ];

        return $statusRu[$status];
    }

    public static function getPaymentMethodTranslate($status)
    {
        $statusRu = [
            'cash' => 'Наличные',
            'card' => 'Карта',
            'insurance_company' => 'Страховая компания',
        ];

        return $statusRu[$status];
    }

    public static function getPlaceTranslate($status)
    {
        $statusRu = [
            'institution' => 'Учреждение',
            'cabinet' => 'Кабинет',
        ];

        return $statusRu[$status];
    }

    public static function getStatusStyles(): array
    {
        return [
            'assigned' => [
                'class' => 'bg-label-success',
                'text' => 'Назначена',
            ],

            'confirmed' => [
                'class' => 'bg-label-success',
                'text' => 'Подтверждено',
            ],
            'in_process' => [
                'class' => 'bg-label-info',
                'text' => 'В процессе',
            ],
            'completed' => [
                'class' => 'bg-label-info',
                'text' => 'Завершена',
            ],
            'cancelled' => [
                'class' => 'bg-label-warning',
                'text' => 'Отменена',
            ],
        ];
    }

    public function getStatusBadge(): string
    {
        $statusStyles = self::getStatusStyles();
        $status = $this->status;

        if (!array_key_exists($status, $statusStyles)) {
            return '<span class="badge bg-label-secondary me-1">Неизвестно</span>';
        }

        $class = $statusStyles[$status]['class'];
        $text = $statusStyles[$status]['text'];

        return "<span class='badge $class me-1'>$text</span>";
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function examinaitonMap(): BelongsTo
    {
        return $this->belongsTo(ExaminationMap::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function equipments(): belongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'patient_service_to_equipments_assignment', 'patient_service_id', 'service_equipment_id');
    }

    public function methods(): belongsToMany
    {
        return $this->belongsToMany(ServiceMethod::class, 'patient_service_to_methods_assignments', 'patient_service_id', 'service_methods_id');
    }

    public function staffs(): belongsToMany
    {
        return $this->belongsToMany(Staff::class, 'patient_service_to_staffs_assignment', 'patient_service_id', 'service_staff_id');
    }

    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponses::class);
    }
}
