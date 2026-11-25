<?php

namespace Database\Seeders;

use App\Models\StaffPosition;
use App\Services\StaffPositionService;
use Database\Seeders\Concerns\SeedsRowsWithFixedIds;
use Illuminate\Database\Seeder;

/**
 * Staff positions with fixed ids, each linked to its role on the "admin" guard; a staff member's
 * login holds the role of their position (StaffService). Code relies on the ids, the doctor
 * position in particular (StaffPosition::DOCTOR). Run after PermissionSeeder: the registrar and
 * cashier positions take over the roles it grants default permissions to.
 */
class StaffPositionSeeder extends Seeder
{
    use SeedsRowsWithFixedIds;

    /** The initial names; PermissionSeeder creates the registrar and cashier roles under these names. */
    public const POSITIONS = [
        StaffPosition::DOCTOR => 'Доктор',
        StaffPosition::HEAD_NURSE => 'Старшая медсестра',
        StaffPosition::NURSE => 'Медсестра',
        StaffPosition::HEAD_OF_DEPARTMENT => 'Заведующий',
        StaffPosition::REGISTRAR => 'Регистратор',
        StaffPosition::CASHIER => 'Кассир',
    ];

    public function run(StaffPositionService $positions): void
    {
        foreach (self::POSITIONS as $id => $name) {
            $positions->roleOf($this->firstOrCreateWithId(StaffPosition::class, $id, ['name' => $name]));
        }

        $this->syncIdSequence('staff_positions');
    }
}
