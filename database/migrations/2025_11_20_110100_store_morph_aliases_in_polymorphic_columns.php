<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

// AppServiceProvider enforces a morph map, so polymorphic *_type columns hold short aliases
// instead of class names. This rewrites the class names already stored. It is idempotent: a value
// that is already an alias is left alone, and down() restores the class names.
return new class extends Migration
{
    /**
     * Frozen copy of the morph map: renaming a class later must not change what this migration does.
     *
     * @var array<string, string>
     */
    private const array ALIASES = [
        'admin' => 'App\Models\Admin',
        'patient' => 'App\Models\Patient',
        'medication' => 'App\Models\Medication',
        'medical_supply' => 'App\Models\MedicalSupply',
        'household_material' => 'App\Models\HouseholdMaterial',
        'main_warehouse_medication' => 'App\Models\MainWarehouseMedication',
        'main_warehouse_medical_supply' => 'App\Models\MainWarehouseMedicalSupply',
        'main_warehouse_household_material' => 'App\Models\MainWarehouseHouseholdMaterial',
        'warehouse_accounting' => 'App\Models\WarehouseAccounting',
        'warehouse_medical_supply' => 'App\Models\WarehouseMedicalSupply',
        'warehouse_household_material' => 'App\Models\WarehouseHouseholdMaterial',
        'pharmacy_medication' => 'App\Models\PharmacyMedication',
        'supply_request' => 'App\Models\SupplyRequest',
        'transfer' => 'App\Models\Transfer',
        'patient_medication' => 'App\Models\PatientMedication',
    ];

    /** @var array<string, list<string>> */
    private const array COLUMNS = [
        'files' => ['fileable_type'],
        'supply_request_items' => ['supplyable_type'],
        'stock_movements' => ['stock_type', 'item_type', 'source_type'],
    ];

    public function up(): void
    {
        $this->rewrite(array_flip(self::ALIASES));
    }

    public function down(): void
    {
        $this->rewrite(self::ALIASES);
    }

    /**
     * @param  array<string, string>  $replacements  stored value => new value
     */
    private function rewrite(array $replacements): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                foreach ($replacements as $from => $to) {
                    DB::table($table)->where($column, $from)->update([$column => $to]);
                }
            }
        }

        foreach ($this->permissionPivots() as $table => $key) {
            foreach ($replacements as $from => $to) {
                $this->rewritePivot($table, $key, $from, $to);
            }
        }
    }

    /**
     * The model_type of an account's roles and permissions is part of the primary key, so a row
     * whose twin already holds the new value is a duplicate: it is dropped instead of renamed.
     */
    private function rewritePivot(string $table, string $key, string $from, string $to): void
    {
        $modelKey = config('permission.column_names.model_morph_key', 'model_id');

        DB::table($table)
            ->where('model_type', $from)
            ->whereNotExists(fn (Builder $twin): Builder => $twin->from($table, 'twin')
                ->where('twin.model_type', $to)
                ->whereColumn("twin.{$modelKey}", "{$table}.{$modelKey}")
                ->whereColumn("twin.{$key}", "{$table}.{$key}"))
            ->update(['model_type' => $to]);

        DB::table($table)->where('model_type', $from)->delete();
    }

    /**
     * spatie/laravel-permission's pivot tables => the key of the role or permission in them.
     *
     * @return array<string, string>
     */
    private function permissionPivots(): array
    {
        return [
            config('permission.table_names.model_has_roles', 'model_has_roles') => config('permission.column_names.role_pivot_key') ?? 'role_id',
            config('permission.table_names.model_has_permissions', 'model_has_permissions') => config('permission.column_names.permission_pivot_key') ?? 'permission_id',
        ];
    }
};
