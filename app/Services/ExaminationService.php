<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\ExaminationMap;
use Illuminate\Support\Facades\DB;

class ExaminationService
{
    private AnalyzeService $service;

    public function __construct(AnalyzeService $service)
    {
        $this->service = $service;
    }

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

            $patient = $map->patient;

            $analyzeData = [
                'patient_id' => $patient->id,
                'doctor_id' => $validated['doctor_id'],
                'examination_map_id' => $map->id,
                'laboratory_id' => $validated['laboratory_id'],
                'types' => $validated['types'],
                'methods' => $validated['methods'],
                'analysis_time' => $validated['analysis_time'],
                'medical_staff' => $validated['medical_staff'],
                'equipments' => $validated['equipments'],
                'status' => 'assigned',
            ];

            $this->service->store($analyzeData);

            return $map;
        });

        return $map;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(ExaminationMap $map, array $validated)
    {
        return DB::transaction(function () use ($validated, $map) {

            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $map->update($validated);

            $requiredFields = ['laboratory_id', 'types', 'methods', 'analysis_time', 'equipments'];

            $anyFieldFilled = collect($requiredFields)->contains(fn($field) => !empty($validated[$field]));

            $allFieldsFilled = collect($requiredFields)->every(fn($field) => !empty($validated[$field]));

            if ($anyFieldFilled && !$allFieldsFilled) {
                $patient = $map->patient;

                $analyzeData = [
                    'patient_id' => $patient->id,
                    'doctor_id' => $validated['doctor_id'],
                    'examination_map_id' => $map->id,
                    'laboratory_id' => $validated['laboratory_id'],
                    'types' => $validated['types'],
                    'methods' => $validated['methods'],
                    'analysis_time' => $validated['analysis_time'],
                    'medical_staff' => $validated['medical_staff'],
                    'equipments' => $validated['equipments'],
                    'status' => 'assigned',
                ];

                $this->service->store($analyzeData);
            }

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
