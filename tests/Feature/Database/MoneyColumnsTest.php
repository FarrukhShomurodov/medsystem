<?php

use App\Models\MainWarehouseMedication;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

/**
 * The precision and scale of a numeric column.
 *
 * @return array{int, int}
 */
function numericColumn(string $table, string $column): array
{
    $definition = DB::selectOne(
        'select numeric_precision, numeric_scale from information_schema.columns where table_name = ? and column_name = ?',
        [$table, $column],
    );

    return [(int) $definition->numeric_precision, (int) $definition->numeric_scale];
}

it('keeps the wide money columns, and the amounts in them, when the widening is rolled back', function (): void {
    // down() narrowed prices back to decimal(8, 2) and retail prices to integer: rolling back over
    // the demo data failed with a numeric overflow (the demo equipment costs 185 000 000), so no
    // earlier migration could be rolled back, and integer would have rounded fractional prices.
    $service = Service::factory()->create(['price' => 185_000_000]);
    $batch = MainWarehouseMedication::factory()->create(['retail_price' => '1250.50']);
    $migration = require database_path('migrations/2025_11_18_100300_widen_money_columns.php');

    $migration->down();

    expect(numericColumn('services', 'price'))->toBe([14, 2])
        ->and(numericColumn('main_warehouse_medications', 'retail_price'))->toBe([14, 2])
        ->and(DB::table('services')->where('id', $service->id)->value('price'))->toBe('185000000.00')
        ->and(DB::table('main_warehouse_medications')->where('id', $batch->id)->value('retail_price'))->toBe('1250.50');

    $migration->up();

    expect(numericColumn('services', 'price'))->toBe([14, 2])
        ->and(numericColumn('main_warehouse_medications', 'incoming_price_per_unit'))->toBe([14, 4])
        ->and(DB::table('services')->where('id', $service->id)->value('price'))->toBe('185000000.00');
});
