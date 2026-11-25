<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Database\Seeders\Concerns\SeedsRowsWithFixedIds;
use Illuminate\Database\Seeder;

/**
 * Service categories used by the price list and its Excel import.
 * Type 1 ("no data") is the fallback the UI refuses to edit (ServiceTypeController::edit()).
 */
class ServiceTypeSeeder extends Seeder
{
    use SeedsRowsWithFixedIds;

    private const array TYPES = [
        'Данные отсутствуют',
        'Общие исследования',
        'Манипуляция',
        'Инструментальные исследования',
        'Лабораторное тестирование',
        'Лечение',
        'Анестезиологические процедуры',
        'Интенсивная терапия',
        'Хирургия',
        'Профилактические процедуры',
        'Консультация',
        'Основные',
        'Лечение медикаментами',
        'Медицинские записи',
        'Сервис почасовой',
        'Технические',
        'Дневной сервис',
        'Имплантация',
        'Иммунизация',
        'Химиотерапия',
        'Питание',
    ];

    public function run(): void
    {
        foreach (self::TYPES as $index => $name) {
            $this->firstOrCreateWithId(ServiceType::class, $index + 1, ['name' => $name]);
        }

        $this->syncIdSequence('service_types');
    }
}
