<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use App\Support\Authorization\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The permissions of PermissionCatalog on the admin guard, plus the built-in roles. Idempotent:
 * existing permissions are kept and staff roles only ever gain permissions, so changes made on the
 * staff position screen survive a re-run. A permission dropped from the catalogue stays in
 * existing databases until `php artisan permissions:prune --force` deletes it.
 */
class PermissionSeeder extends Seeder
{
    /** Default permissions of the roles of staff positions, by the fixed position id. */
    private const array POSITION_PERMISSIONS = [
        StaffPosition::REGISTRAR => [
            'view_dashboard',
            'view_patients',
            'create_patients',
            'edit_patients',
            'delete_patients',
            'patient_inspection_store',
            'create_patient_map',
            'create_patient_services',
            'edit_patient_services',
            'delete_patient_services',
            'confirm_patient_registration',
            'view_cashier',
        ],
        StaffPosition::CASHIER => [
            'view_dashboard',
            'view_cashier',
            'accept_payments',
        ],
    ];

    public function run(StaffPositionService $positions): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        $this->createMissingPermissions();

        Role::findOrCreate(Admin::MAIN_ADMIN_ROLE, Admin::GUARD)
            ->syncPermissions(Permission::query()->where('guard_name', Admin::GUARD)->get());

        foreach (self::POSITION_PERMISSIONS as $positionId => $permissions) {
            $this->roleOfPosition($positionId, $positions)->givePermissionTo($permissions);
        }

        $registrar->forgetCachedPermissions();
    }

    /**
     * Inserts the permissions the database does not hold yet, in list order and in one statement.
     * Permission::findOrCreate() per name would reload the whole permission cache (every
     * permission with its roles) after each insert, which made seeding a fresh database slow.
     */
    private function createMissingPermissions(): void
    {
        $existing = Permission::query()->where('guard_name', Admin::GUARD)->pluck('name')->all();
        $now = now();

        Permission::query()->insert(array_map(
            fn (string $name): array => ['name' => $name, 'guard_name' => Admin::GUARD, 'created_at' => $now, 'updated_at' => $now],
            array_values(array_diff(PermissionCatalog::names(), $existing)),
        ));
    }

    /**
     * A position's role is renamed with the position, so on a re-run it is found through the
     * position. On a fresh database the positions do not exist yet: the role is created under the
     * position's initial name, and StaffPositionSeeder links the position to it.
     */
    private function roleOfPosition(int $positionId, StaffPositionService $positions): Role
    {
        $position = StaffPosition::query()->find($positionId);

        return $position !== null
            ? $positions->roleOf($position)
            : Role::query()->firstOrCreate(['name' => StaffPositionSeeder::POSITIONS[$positionId], 'guard_name' => Admin::GUARD]);
    }
}
