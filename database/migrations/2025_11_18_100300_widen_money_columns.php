<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Laravel's decimal() defaults to (8, 2), which caps amounts at 999 999.99: too small for UZS prices.
// Every money column becomes decimal(14, 2); the per-unit purchase price keeps its 4-digit scale.
return new class extends Migration
{
    private const array PRICE_TABLES = ['services', 'procedures', 'equipment', 'patient_medications'];

    public function up(): void
    {
        foreach (self::PRICE_TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->decimal('price', 14, 2)->change();
            });
        }

        Schema::table('household_materials', function (Blueprint $table): void {
            $table->decimal('entry_price_total', 14, 2)->change();
            $table->decimal('entry_price_unit', 14, 2)->change();
        });

        Schema::table('medication_cost_and_supplies', function (Blueprint $table): void {
            $table->decimal('retail_price', 14, 2)->change();
            $table->decimal('wholesale_price', 14, 2)->change();
        });

        Schema::table('main_warehouse_medications', function (Blueprint $table): void {
            $table->decimal('retail_price', 14, 2)->nullable()->change();
            $table->decimal('wholesale_price', 14, 2)->nullable()->change();
            $table->decimal('total_price', 14, 2)->nullable()->change();
            $table->decimal('incoming_price_total', 14, 2)->nullable()->change();
            $table->decimal('incoming_price_per_unit', 14, 4)->nullable()->change();
            $table->decimal('incoming_sum', 14, 2)->nullable()->change();
        });
    }

    /**
     * Deliberately keeps the wide columns. decimal(8, 2) overflows on any UZS amount from 1 000 000
     * up (the demo equipment costs 185 000 000), so narrowing back failed over real data and blocked
     * every earlier rollback; integer would also have rounded fractional prices. The wider columns
     * work with every earlier migration and the earlier code, and up() is safe to run over them again.
     */
    public function down(): void
    {
        // Nothing to undo without losing amounts.
    }
};
