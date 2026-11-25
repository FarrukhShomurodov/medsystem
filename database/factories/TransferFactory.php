<?php

namespace Database\Factories;

use App\Enums\TransferStatus;
use App\Models\Pharmacy;
use App\Models\Transfer;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A pending transfer from a department warehouse to a pharmacy (without lines).
 *
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from_warehouse_id' => Warehouse::factory(),
            'to_pharmacy_id' => Pharmacy::factory(),
            'transfer_date' => now()->toDateString(),
            'status' => TransferStatus::Pending,
            'notes' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['status' => TransferStatus::Completed]);
    }
}
