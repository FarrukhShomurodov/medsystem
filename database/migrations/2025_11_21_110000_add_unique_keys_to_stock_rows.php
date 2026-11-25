<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A stock row holds one item in one place. StockLedger::lot() looks the row up FOR UPDATE, which
 * locks nothing while the row does not exist yet: two first receipts of a supply, or two transfers
 * into a pharmacy that never held the drug, each inserted a row of their own. With this key the
 * second insert waits for the first transaction and then finds its row.
 *
 * Duplicates already written are merged into the oldest row of their key: the quantities are
 * summed, and the ledger's movements (and, for department batches, their packaging and cost
 * rows) move to it, so a returned prescription still finds the batch it came from.
 * Main-warehouse medications stay out: they are one batch per receipt line by design.
 */
return new class extends Migration
{
    /** table => [morph alias of its rows (stock_movements.stock_type), the columns of the key] */
    private const array KEYS = [
        'pharmacy_medications' => ['pharmacy_medication', ['pharmacy_id', 'medication_id']],
        'warehouse_medical_supplies' => ['warehouse_medical_supply', ['warehouse_id', 'supply_id']],
        'warehouse_household_materials' => ['warehouse_household_material', ['warehouse_id', 'household_material_id']],
        'main_warehouse_medical_supplies' => ['main_warehouse_medical_supply', ['main_warehouse_id', 'supply_id']],
        'main_warehouse_household_materials' => ['main_warehouse_household_material', ['main_warehouse_id', 'household_material_id']],
        'warehouse_accountings' => ['warehouse_accounting', ['warehouse_id', 'medication_id', 'batch_number', 'expiration_date']],
    ];

    /** The department batches, whose packaging and cost rows point to them. */
    private const string BATCHES = 'warehouse_accountings';

    private const array BATCH_REFERENCES = ['medication_packagings', 'medication_cost_and_supplies'];

    public function up(): void
    {
        foreach (self::KEYS as $table => [$alias, $columns]) {
            $this->mergeDuplicates($table, $alias, $columns);

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns): void {
                // A batch without a number is still one batch (batch_number is nullable).
                $blueprint->unique($columns, "{$table}_one_row_per_item")->nullsNotDistinct();
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::KEYS) as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                $blueprint->dropUnique("{$table}_one_row_per_item");
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function mergeDuplicates(string $table, string $alias, array $columns): void
    {
        $key = implode(', ', $columns);
        $isBatch = $table === self::BATCHES;

        // PARTITION BY and GROUP BY put NULLs together, as the NULLS NOT DISTINCT key does.
        DB::statement(<<<SQL
            CREATE TEMPORARY TABLE stock_row_duplicates AS
            SELECT id, keep_id
            FROM (SELECT id, MIN(id) OVER (PARTITION BY {$key}) AS keep_id FROM {$table}) AS ranked
            WHERE id <> keep_id
            SQL);

        $receipt = $isBatch ? ['set' => ', date_of_receipt = merged.date_of_receipt', 'select' => ', MIN(date_of_receipt) AS date_of_receipt'] : ['set' => '', 'select' => ''];

        DB::statement(<<<SQL
            UPDATE {$table}
            SET quantity = merged.quantity{$receipt['set']}
            FROM (
                SELECT MIN(id) AS keep_id, SUM(quantity) AS quantity{$receipt['select']}
                FROM {$table}
                GROUP BY {$key}
                HAVING COUNT(*) > 1
            ) AS merged
            WHERE {$table}.id = merged.keep_id
            SQL);

        DB::update(<<<'SQL'
            UPDATE stock_movements
            SET stock_id = duplicates.keep_id
            FROM stock_row_duplicates AS duplicates
            WHERE stock_movements.stock_type = ? AND stock_movements.stock_id = duplicates.id
            SQL, [$alias]);

        foreach ($isBatch ? self::BATCH_REFERENCES : [] as $references) {
            DB::statement(<<<SQL
                UPDATE {$references}
                SET warehouse_accounting_id = duplicates.keep_id
                FROM stock_row_duplicates AS duplicates
                WHERE {$references}.warehouse_accounting_id = duplicates.id
                SQL);
        }

        DB::statement("DELETE FROM {$table} WHERE id IN (SELECT id FROM stock_row_duplicates)");
        DB::statement('DROP TABLE stock_row_duplicates');
    }
};
