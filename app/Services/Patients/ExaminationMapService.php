<?php

namespace App\Services\Patients;

use App\Exceptions\Patients\ActiveExaminationMapException;
use App\Exceptions\Patients\DeletionRefusedException;
use App\Models\ExaminationMap;
use App\Models\Patient;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExaminationMapService
{
    public function __construct(private readonly RetainedRecords $retainedRecords) {}

    /**
     * Opens a hospital stay (or records a finished one when the discharge date is already known)
     * and attaches the patient's latest initial nursing assessment that is not tied to a stay yet.
     *
     * @param  array<string, mixed>  $validated
     *
     * @throws ActiveExaminationMapException
     */
    public function store(array $validated): ExaminationMap
    {
        return DB::transaction(function () use ($validated): ExaminationMap {
            // The row lock serialises admissions of one patient: a double submit waits here and
            // then sees the stay the first request opened.
            $patient = Patient::query()->lockForUpdate()->findOrFail((int) $validated['patient_id']);
            $opensStay = blank($validated['discharge_date'] ?? null);

            if ($opensStay && $patient->activeExaminationMap()->exists()) {
                throw new ActiveExaminationMapException;
            }

            $map = $patient->examinationMaps()->create([
                ...Arr::except($validated, 'patient_id'),
                'is_active' => $opensStay,
            ]);

            $patient->pendingInitialAssessment()?->update(['examination_map_id' => $map->id]);

            return $map;
        });
    }

    /**
     * Saves the map; a discharge date closes the stay. The patient of a map never changes.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(ExaminationMap $map, array $validated): ExaminationMap
    {
        $attributes = Arr::except($validated, 'patient_id');

        if (filled($validated['discharge_date'] ?? null)) {
            $attributes['is_active'] = false;
        }

        $map->update($attributes);

        return $map;
    }

    /**
     * @throws DeletionRefusedException when the stay holds paid orders or dispensed drugs
     */
    public function destroy(ExaminationMap $map): void
    {
        DB::transaction(function () use ($map): void {
            $this->retainedRecords->ensureStayDeletable($map);
            $map->delete();
        });
    }
}
