<?php

namespace Database\Factories;

use App\Models\SurveyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyType>
 */
class SurveyTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Анкета', 'Опросник', 'Чек-лист']),
        ];
    }
}
