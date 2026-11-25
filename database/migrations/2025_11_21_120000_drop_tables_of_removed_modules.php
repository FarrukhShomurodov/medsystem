<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The tables of the modules 2.0.0 removed: laboratory analyses, the "users" accounts of the
 * removed web guard, warehouse transfers and inventories, and images. No code reads them, yet
 * their keys to staff, equipment and suppliers refused deletes that no screen could explain.
 *
 * A database upgraded from 1.x may still hold rows in them, laboratory records among them. Those
 * are never dropped silently: the migration stops and names the tables to export and empty first.
 * The framework's sessions table, created by the same original migration as users, stays.
 */
return new class extends Migration
{
    /** In drop order, children first: table => the original migration that creates it. */
    private const array TABLES = [
        'analyze_results' => '2025_01_25_222819_create_analyze_results_table',
        'analyze_to_type_assignment' => '2025_01_25_190455_create_analyze_to_type_assignment_table',
        'analyze_to_equipments_assignment' => '2025_01_25_172252_create_analyze_to_equipments_assignment_table',
        'analyze_to_methods_assignment' => '2025_01_25_172230_create_analyze_to_methods_assignment_table',
        'analyzes' => '2025_01_22_204904_create_analyzes_table',
        'analyze_types' => '2025_01_24_190205_create_analyze_types_table',
        'analyze_methods' => '2025_01_24_190304_create_analyze_methods_table',
        'laboratories' => '2025_01_22_191704_create_laboratories_table',
        'warehouse_transfers' => '2025_02_24_164617_create_warehouse_transfers_table',
        'warehouse_inventories' => '2025_02_25_022000_create_warehouse_inventories_table',
        'images' => '2024_10_24_195912_create_images_table',
        // Its original migration also creates sessions, so users is recreated here.
        'users' => null,
    ];

    public function up(): void
    {
        $holdingRows = array_filter(
            array_keys(self::TABLES),
            fn (string $table): bool => Schema::hasTable($table) && DB::table($table)->exists(),
        );

        if ($holdingRows !== []) {
            throw new RuntimeException(sprintf(
                'Tables of removed modules still hold rows: %s. Export them (pg_dump --data-only --table=...), empty them, then migrate again.',
                implode(', ', $holdingRows),
            ));
        }

        foreach (array_keys(self::TABLES) as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $migration) {
            if ($migration === null) {
                $this->createUsersTable();
            } else {
                $this->runOriginal($migration);
            }
        }
    }

    private function runOriginal(string $migration): void
    {
        $original = require __DIR__."/{$migration}.php";
        assert($original instanceof Migration && method_exists($original, 'up'));

        $original->up();
    }

    private function createUsersTable(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('login')->unique();
            $table->string('password');
            $table->foreignId('staff_id')->constrained('staff');
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
