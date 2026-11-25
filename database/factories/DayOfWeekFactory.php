<?php

namespace Database\Factories;

use App\Models\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Real databases get the seven days from DayOfWeekSeeder; this factory serves isolated tests.
 *
 * @extends Factory<DayOfWeek>
 */
class DayOfWeekFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье']),
        ];
    }
}
