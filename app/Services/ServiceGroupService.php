<?php

namespace App\Services;

use App\Models\ServiceGroup;
use Illuminate\Support\Facades\DB;

class ServiceGroupService
{
    /**
     * @param array<string, mixed> $validated
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
     * @param ServiceGroup $group
     * @param array<string, mixed> $validated
     * @return ServiceGroup
     */
    public function update(ServiceGroup $group, array $validated): ServiceGroup
    {
        return DB::transaction(function () use ($group, $validated) {
            $group->update([
                'name' => $validated['name'],
            ]);

            $submittedIds = [];

            foreach ($validated['sub_groups'] as $sub_group) {
                if (isset($sub_group['id'])) {
                    $group->subGroups()->updateOrCreate(
                        ['id' => $sub_group['id']],
                        ['name' => $sub_group['name']]
                    );
                    $submittedIds[] = $sub_group['id'];
                } else {
                    $newBed = $group->subGroups()->create([
                        'name' => $sub_group['name'],
                    ]);
                    $submittedIds[] = $newBed->id;
                }
            }

            $group->subGroups()->whereNotIn('id', $submittedIds)->delete();

            return $group->refresh();
        });
    }

    /**
     * @param ServiceGroup $group
     * @return void
     */
    public function destroy(ServiceGroup $group): void
    {
        DB::transaction(function () use ($group) {
            $group->subGroups()->delete();
            $group->delete();
        });
    }
}
