<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * The scored medical documents themselves come from DocumentSeeder; code relies on their ids.
 *
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Лист оценки '.fake()->randomElement(['риска пролежней', 'питания', 'состояния кожи']),
        ];
    }
}
