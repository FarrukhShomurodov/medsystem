<?php

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Анкета пациента с гипертензией', 'Опросник качества сна', 'Анкета перед операцией']),
            'color' => fake()->randomElement(Survey::COLOR),
            'survey_type_id' => SurveyType::factory(),
        ];
    }
}
