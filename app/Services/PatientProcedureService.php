<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Models\Patient;
use App\Models\PatientProcedure;
use App\Models\Procedure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PatientProcedureService
{
    /**
     * One order per submitted line, so the same procedure on two dates is two orders. They join
     * the patient's active examination map, if any, wait for the registrar as "assigned" and keep
     * the price they were ordered at.
     *
     * @param  array{patient_id: int, procedures: array<int, int>, procedure_dates: array<int, string>}  $validated
     * @return Collection<int, PatientProcedure>
     */
    public function store(array $validated): Collection
    {
        $patient = Patient::query()->findOrFail($validated['patient_id']);
        $mapId = $patient->activeExaminationMap()->value('id');
        $dates = array_values($validated['procedure_dates']);
        $prices = Procedure::query()->whereKey($validated['procedures'])->pluck('price', 'id');

        return DB::transaction(fn (): Collection => collect(array_values($validated['procedures']))
            ->map(fn (int|string $procedureId, int $line): PatientProcedure => PatientProcedure::query()->create([
                'patient_id' => $patient->id,
                'examination_map_id' => $mapId,
                'procedure_id' => (int) $procedureId,
                'procedure_date' => $dates[$line],
                'status' => ServiceStatus::Assigned,
                'price' => $prices[(int) $procedureId],
            ])));
    }
}
