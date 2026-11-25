<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Терапевтическое отделение',
                'Кардиологическое отделение',
                'Неврологическое отделение',
                'Хирургическое отделение',
                'Педиатрическое отделение',
            ]),
            'floor_id' => Floor::factory(),
        ];
    }
}
