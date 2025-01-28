<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\ExaminationMap;
use Illuminate\Support\Facades\DB;

class ExaminationService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ExaminationMap
    {
        $map = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }


            $map = ExaminationMap::query()->create($validated);

            return $map;
        });

        return $map;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ExaminationMap $patient, array $validated)
    {
        return DB::transaction(function () use ($validated, $patient) {

            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $map = ExaminationMap::query()->update($validated);

            return $map;
        });
    }

    /**
     * @param ExaminationMap $map
     * @return void
     */
    public function destroy(ExaminationMap $map): void
    {
        $map->delete();
    }
}
