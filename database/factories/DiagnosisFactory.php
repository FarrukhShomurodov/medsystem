<?php

namespace Database\Factories;

use App\Models\Diagnosis;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnosis>
 */
class DiagnosisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('[A-T][0-9]{2}\.[0-9]'),
            'name' => fake()->randomElement([
                'Острый назофарингит',
                'Эссенциальная гипертензия',
                'Хронический гастрит',
                'Сахарный диабет 2 типа',
            ]),
        ];
    }
}
