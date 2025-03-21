<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Medication extends Model
{

    protected $fillable = [
        'name',
        'release_form',
        'dosage',
        'manufacturer_id',
        'registration_number',
        'registration_date',
    ];

    const RELEASEFORM = [
        "Таблетка",
        "Ампула",
        "Жидкость",
        "Сироп",
        "Капсула",
        "Порошок",
        "Гранулы",
        "Экстракт",
        "Пастилка",
        "Спрей",
        "Раствор",
        "Свечи ректальные",
        "Мазь",
        "Гель",
        "Пакетик",
        "Крем",
        "Капли",
        "Масло",
        "Глазные капли",
        "Свечи вагинальные",
        "Ушные капли",
        "Суспензия",
        "Пластырь",
        "Настойка",
        "Паста",
        "Флакон",
        "Эластичный бинт",
        "Шприц",
        "Вата",
        "Саше",
        "Рулон",
        "Бинт",
        "Система",
        "Бальзам"
    ];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function application(): HasOne
    {
        return $this->hasOne(MedicationApplication::class);
    }

    public function additionalData(): HasOne
    {
        return $this->hasOne(MedicationAdditionalData::class);
    }

    public function riskAndSafety(): HasOne
    {
        return $this->hasOne(MedicationRiskAndSafety::class);
    }

    public function composition(): HasOne
    {
        return $this->hasOne(MedicationComposition::class);
    }

    public function storage(): HasOne
    {
        return $this->hasOne(MedicationStorage::class);
    }

    public function licenseAndCertification(): HasOne
    {
        return $this->hasOne(MedicationLicenseAndCertification::class);
    }
}
