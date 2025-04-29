<?php

namespace App\Services;

use App\Models\StaffPosition;
use Spatie\Permission\Models\Role;

class StaffPositionService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): StaffPosition
    {
        $position = StaffPosition::query()->create($validated);

        $role = Role::firstOrCreate([
            'name' => $position->name,
            'guard_name' => 'admin',
        ]);

        $role->syncPermissions($validated['permissions']);

        return $position;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(StaffPosition $position, array $validated): StaffPosition
    {
        $position->update($validated);

        $role = Role::query()->updateOrCreate(
            [
                'name' => $position->name,
                'guard_name' => 'admin',
            ],
            [] // Если не нужно обновлять другие поля, можно оставить пустым
        );

        $role->syncPermissions($validated['permissions']);

        return $position->refresh();
    }

    /**
     * @param StaffPosition $position
     * @return void
     */
    public function destroy(StaffPosition $position): void
    {
        $position->delete();
    }
}
