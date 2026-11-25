<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Deleting a department warehouse or a pharmacy cascaded into its stock rows and its supply
// requests: units vanished without a write-off, and the ledger kept pointing at rows that no
// longer existed. A place that holds or ordered stock can no longer be deleted; the delete is
// answered with "Запись нельзя удалить" (bootstrap/app.php) instead.
return new class extends Migration
{
    /** table => [column => referenced table] */
    private const array KEYS = [
        'warehouse_accountings' => ['warehouse_id' => 'warehouses'],
        'warehouse_medical_supplies' => ['warehouse_id' => 'warehouses'],
        'warehouse_household_materials' => ['warehouse_id' => 'warehouses'],
        'supply_requests' => ['warehouse_id' => 'warehouses'],
        'pharmacy_medications' => ['pharmacy_id' => 'pharmacies'],
    ];

    public function up(): void
    {
        $this->replaceForeignKeys('restrict');
    }

    public function down(): void
    {
        $this->replaceForeignKeys('cascade');
    }

    private function replaceForeignKeys(string $onDelete): void
    {
        foreach (self::KEYS as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $onDelete): void {
                foreach ($columns as $column => $referencedTable) {
                    $table->dropForeign([$column]);
                    $table->foreign($column)->references('id')->on($referencedTable)->onDelete($onDelete);
                }
            });
        }
    }
};
