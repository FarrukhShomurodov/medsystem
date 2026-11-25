<?php

namespace App\Services;

use App\Models\ServiceGroup;
use Illuminate\Support\Facades\DB;

class ServiceGroupService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): ServiceGroup
    {
        return DB::transaction(function () use ($validated) {
            $group = ServiceGroup::query()->create([
                'name' => $validated['name'],
            ]);

            foreach ($validated['sub_groups'] as $sub_group) {
                $group->subGroups()->create([
                    'name' => $sub_group,
                ]);
            }

            return $group;
        });
    }

    /**
     * Saves the subgroups as the form lists them: one with an id is renamed (ServiceGroupRequest
     * has checked it is one of this group's), one without is added, and the subgroups the form no
     * longer lists are deleted. Only the ids of rows saved here are kept, never a posted one.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(ServiceGroup $group, array $validated): ServiceGroup
    {
        return DB::transaction(function () use ($group, $validated) {
            $group->update([
                'name' => $validated['name'],
            ]);

            $savedIds = [];

            foreach ($validated['sub_groups'] as $subGroup) {
                $row = isset($subGroup['id']) ? $group->subGroups()->whereKey($subGroup['id'])->firstOrFail() : $group->subGroups()->make();
                $row->fill(['name' => $subGroup['name']])->save();
                $savedIds[] = $row->id;
            }

            $group->subGroups()->whereNotIn('id', $savedIds)->delete();

            return $group->refresh();
        });
    }

    public function destroy(ServiceGroup $group): void
    {
        DB::transaction(function () use ($group): void {
            $group->subGroups()->delete();
            $group->delete();
        });
    }
}
