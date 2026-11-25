<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// equipment.status and the packaging_type columns stored their Russian labels. They now store the
// values of App\Enums\EquipmentStatus and App\Enums\PackagingType, which carry the labels. On
// PostgreSQL Laravel enums are varchar + CHECK, so each constraint is replaced around the rewrite.
// Running it again changes nothing; down() puts the Russian values and constraints back.
return new class extends Migration
{
    /** @var array<string, array<string, array<string, string>>> table => column => old value => new value */
    private const array VALUES = [
        'equipment' => [
            'status' => ['работает' => 'working', 'сломан' => 'broken'],
        ],
        'medication_packagings' => [
            'packaging_type' => ['блистер' => 'blister', 'флакон' => 'vial', 'тюбик' => 'tube'],
        ],
        'main_warehouse_medications' => [
            'packaging_type' => ['блистер' => 'blister', 'флакон' => 'vial', 'тюбик' => 'tube'],
        ],
    ];

    public function up(): void
    {
        $this->rewrite(fn (array $values): array => $values);
    }

    public function down(): void
    {
        $this->rewrite(fn (array $values): array => array_flip($values));
    }

    /**
     * @param  callable(array<string, string>): array<string, string>  $direction
     */
    private function rewrite(callable $direction): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::VALUES as $table => $columns) {
            foreach ($columns as $column => $values) {
                $values = $direction($values);
                $constraint = "{$table}_{$column}_check";
                $allowed = implode(', ', array_map(fn (string $value): string => DB::getPdo()->quote($value), $values));

                DB::statement("alter table {$table} drop constraint if exists {$constraint}");

                foreach ($values as $from => $to) {
                    DB::table($table)->where($column, $from)->update([$column => $to]);
                }

                DB::statement("alter table {$table} add constraint {$constraint} check ({$column} in ({$allowed}))");
            }
        }
    }
};
