<?php

namespace Database\Seeders;

use App\Models\MainWarehouse;
use App\Models\WarehouseType;
use Database\Seeders\Concerns\SeedsRowsWithFixedIds;
use Illuminate\Database\Seeder;

/**
 * Warehouse types and the main warehouse. Their ids are part of the contract with the code: the
 * main warehouse is MainWarehouse::MAIN, and the type ids are WarehouseType::MEDICATIONS,
 * HOUSEHOLD_MATERIALS and MEDICAL_SUPPLIES, which WarehouseType::STOCK_ITEMS maps to the
 * catalogue each type stores.
 */
class WarehouseSeeder extends Seeder
{
    use SeedsRowsWithFixedIds;

    private const array WAREHOUSE_TYPES = [
        WarehouseType::MEDICATIONS => 'Медикаменты',
        WarehouseType::HOUSEHOLD_MATERIALS => 'Хозяйственный материал',
        WarehouseType::MEDICAL_SUPPLIES => 'ИМН',
    ];

    public function run(): void
    {
        foreach (self::WAREHOUSE_TYPES as $id => $name) {
            $this->firstOrCreateWithId(WarehouseType::class, $id, ['name' => $name]);
        }

        $this->firstOrCreateWithId(MainWarehouse::class, MainWarehouse::MAIN, ['name' => 'Общий склад']);

        $this->syncIdSequence('warehouse_types');
        $this->syncIdSequence('main_warehouses');
    }
}
