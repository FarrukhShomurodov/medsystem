<?php

namespace Database\Factories;

use App\Models\Procedure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Procedure>
 */
class ProcedureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Внутривенная инъекция', 'Внутримышечная инъекция', 'Перевязка', 'Капельница']),
            'price' => fake()->numberBetween(2, 20) * 10_000,
        ];
    }
}
