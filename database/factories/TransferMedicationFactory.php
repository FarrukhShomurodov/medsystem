<?php

namespace Database\Factories;

use App\Models\Medication;
use App\Models\Transfer;
use App\Models\TransferMedication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferMedication>
 */
class TransferMedicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transfer_id' => Transfer::factory(),
            'medication_id' => Medication::factory(),
            'quantity' => fake()->numberBetween(1, 20),
        ];
    }
}
