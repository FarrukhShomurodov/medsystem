<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| The tables of the modules 2.0.0 removed (laboratory analyses, the "users" accounts, warehouse
| transfers and inventories, images): no code reads them, and their keys to staff, equipment and
| suppliers refused deletes the dashboard could not explain.
*/

/**
 * @return list<string>
 */
function removedModuleTables(): array
{
    return [
        'analyze_results', 'analyze_to_type_assignment', 'analyze_to_equipments_assignment',
        'analyze_to_methods_assignment', 'analyzes', 'analyze_types', 'analyze_methods', 'laboratories',
        'warehouse_transfers', 'warehouse_inventories', 'images', 'users',
    ];
}

function dropRemovedModuleTablesMigration(): object
{
    return require database_path('migrations/2025_11_21_120000_drop_tables_of_removed_modules.php');
}

it('drops the tables of the removed modules and keeps the sessions table', function (): void {
    foreach (removedModuleTables() as $table) {
        expect(Schema::hasTable($table))->toBeFalse("{$table} still exists");
    }

    // sessions comes from the same original migration as users, and the framework uses it.
    expect(Schema::hasTable('sessions'))->toBeTrue();
});

it('recreates the tables on rollback and refuses to drop one that still holds rows', function (): void {
    $migration = dropRemovedModuleTablesMigration();

    $migration->down();

    foreach (removedModuleTables() as $table) {
        expect(Schema::hasTable($table))->toBeTrue("{$table} was not recreated");
    }

    // A database upgraded from 1.x may still hold its laboratory records: they are never dropped
    // silently, the operator exports and empties the table first.
    DB::table('laboratories')->insert(['name' => 'Центральная лаборатория', 'created_at' => now(), 'updated_at' => now()]);

    expect(fn () => $migration->up())->toThrow(RuntimeException::class, 'laboratories');
    expect(Schema::hasTable('analyzes'))->toBeTrue();

    DB::table('laboratories')->delete();
    $migration->up();

    expect(Schema::hasTable('laboratories'))->toBeFalse()
        ->and(Schema::hasTable('users'))->toBeFalse();
});
