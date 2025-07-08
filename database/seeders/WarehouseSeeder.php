<?php

namespace Database\Seeders;

use App\Models\MainWarehouse;
use App\Models\WarehouseType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $medication = WarehouseType::query()->create(['name' => 'Медикаменты']);
        $householdMaterial = WarehouseType::query()->create(['name' => 'Хозяйственный материал']);
        $householdMaterial = WarehouseType::query()->create(['name' => 'ИМН']);

        MainWarehouse::query()->create(['name' => 'Общий склад']);
    }
}
