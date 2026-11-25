<?php

namespace Database\Seeders;

use App\Models\VitalParameter;
use Illuminate\Database\Seeder;

/**
 * Vital signs recorded at the nurse station; VitalSignsChart maps them by these exact names.
 */
class VitalParameterSeeder extends Seeder
{
    private const array PARAMETERS = [
        'Систолическое АД',
        'Диастолическое АД',
        'Пульс',
        'Температура',
        'Сатурация (SPO₂)',
        'Частота дыхания (ЧДД)',
        'Уровень сознания',
        'Кислород/воздух',
        'Диурез / Баланс',
    ];

    public function run(): void
    {
        foreach (self::PARAMETERS as $name) {
            VitalParameter::query()->firstOrCreate(['name' => $name]);
        }
    }
}
