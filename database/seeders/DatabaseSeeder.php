<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Reference data the application cannot work without; every seeder here is safe to run again.
     * Demo data is added on local machines only.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            StaffPositionSeeder::class,
            AdminSeeder::class,
            DayOfWeekSeeder::class,
            ServiceTypeSeeder::class,
            VitalParameterSeeder::class,
            WarehouseSeeder::class,
            DocumentSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
