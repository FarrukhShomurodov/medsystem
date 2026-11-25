<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Stock is checked and decremented under row locks, but the database is the last line of
// defence: a quantity on hand never goes below zero and an ordered quantity is always positive.
// NOT VALID enforces the checks for every new or changed row without rejecting the migration
// over old rows that are already wrong.
return new class extends Migration
{
    /** table => [constraint => condition] */
    private const array CHECKS = [
        'main_warehouse_medications' => ['quantity_not_negative' => 'quantity >= 0'],
        'main_warehouse_medical_supplies' => ['quantity_not_negative' => 'quantity >= 0'],
        'main_warehouse_household_materials' => ['quantity_not_negative' => 'quantity >= 0'],
        'warehouse_accountings' => ['quantity_not_negative' => 'quantity >= 0'],
        'warehouse_medical_supplies' => ['quantity_not_negative' => 'quantity >= 0'],
        'warehouse_household_materials' => ['quantity_not_negative' => 'quantity >= 0'],
        'pharmacy_medications' => ['quantity_not_negative' => 'quantity >= 0'],
        'transfer_medications' => ['quantity_positive' => 'quantity > 0'],
        'supply_request_items' => ['requested_quantity_positive' => 'requested_quantity > 0'],
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::CHECKS as $table => $checks) {
            foreach ($checks as $name => $condition) {
                DB::statement("alter table {$table} add constraint {$table}_{$name} check ({$condition}) not valid");
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::CHECKS as $table => $checks) {
            foreach (array_keys($checks) as $name) {
                DB::statement("alter table {$table} drop constraint if exists {$table}_{$name}");
            }
        }
    }
};
