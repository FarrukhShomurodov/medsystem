<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\StaffPosition;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * A position and its role are created, renamed and deleted together; the position's permissions
 * are the permissions of its role.
 */
class StaffPositionService
{
    /**
     * @param  array{name: string, permissions: list<string>}  $validated
     */
    public function store(array $validated): StaffPosition
    {
        return DB::transaction(function () use ($validated): StaffPosition {
            $role = Role::query()->create(['name' => $validated['name'], 'guard_name' => Admin::GUARD]);
            $role->syncPermissions($validated['permissions']);

            $position = new StaffPosition(['name' => $validated['name']]);
            $position->role()->associate($role)->save();

            return $position;
        });
    }

    /**
     * @param  array{name: string, permissions: list<string>}  $validated
     */
    public function update(StaffPosition $position, array $validated): StaffPosition
    {
        return DB::transaction(function () use ($position, $validated): StaffPosition {
            $role = $this->roleOf($position);

            $position->update(['name' => $validated['name']]);
            $role->update(['name' => $validated['name']]);
            $role->syncPermissions($validated['permissions']);

            return $position->refresh();
        });
    }

    /**
     * Staff still holding the position keep it from being deleted (ON DELETE RESTRICT).
     */
    public function destroy(StaffPosition $position): void
    {
        DB::transaction(function () use ($position): void {
            $role = $position->role;

            $position->delete();
            $role?->delete();
        });
    }

    /**
     * The position's role. A position without one (created before positions were linked to roles
     * by id, or by a factory) is linked on first use: to the role of the same name unless that is
     * a system role or belongs to another position, otherwise to a new role.
     */
    public function roleOf(StaffPosition $position): Role
    {
        if ($position->role !== null) {
            return $position->role;
        }

        return DB::transaction(function () use ($position): Role {
            $locked = StaffPosition::query()->lockForUpdate()->findOrFail($position->id);

            $role = $locked->role
                ?? $this->unlinkedRoleNamed($locked->name)
                ?? Role::query()->create(['name' => $this->freeRoleName($locked), 'guard_name' => Admin::GUARD]);

            $locked->role()->associate($role)->save();

            $position->role()->associate($role);
            $position->syncOriginalAttribute('role_id');

            return $role;
        });
    }

    private function unlinkedRoleNamed(string $name): ?Role
    {
        if (in_array($name, StaffPosition::RESERVED_ROLE_NAMES, true)) {
            return null;
        }

        return Role::query()
            ->where('guard_name', Admin::GUARD)
            ->where('name', $name)
            ->whereNotIn('id', StaffPosition::query()->whereNotNull('role_id')->select('role_id'))
            ->first();
    }

    private function freeRoleName(StaffPosition $position): string
    {
        $taken = Role::query()->where('guard_name', Admin::GUARD)->where('name', $position->name)->exists();

        return $taken ? "{$position->name} #{$position->getKey()}" : $position->name;
    }
}
