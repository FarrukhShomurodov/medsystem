<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceGroup;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Общий анализ крови', 'ЭКГ', 'УЗИ брюшной полости', 'Рентгенография грудной клетки']),
            'code' => fake()->bothify('A##.###'),
            'price' => fake()->numberBetween(5, 60) * 10_000,
            'currency' => 'UZS',
            'service_type_id' => ServiceType::factory(),
            'service_group_id' => ServiceGroup::factory(),
            'is_active' => true,
            'is_appointment' => false,
        ];
    }

    /** A service patients book as a timed appointment with a doctor (a consultation). */
    public function appointment(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Консультация '.fake()->randomElement(['терапевта', 'кардиолога', 'невролога', 'хирурга']),
            'is_appointment' => true,
        ]);
    }
}
