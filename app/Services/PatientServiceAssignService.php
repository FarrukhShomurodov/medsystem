<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\Institution;
use App\Models\PatientService;
use App\Models\Procedure;
use App\Models\ServiceMethod;
use Illuminate\Support\Facades\DB;

class PatientServiceAssignService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PatientService
    {
        $service = DB::transaction(function () use ($validated) {
            if (isset($validated['institution_id']) && !is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $service = PatientService::query()->create($validated);

            //  methods
            if (isset($validated['methods'])) {
                foreach ($validated['methods'] as $key => $method) {
                    if (!is_numeric($method)) {
                        $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
                    }
                }

                $service->methods()->sync($validated['methods']);
            }


            //  equipments
            if (isset($validated['equipments'])) {
                foreach ($validated['equipments'] as $key => $equipment) {
                    if (!is_numeric($equipment)) {
                        $validated['equipments'][$key] = Equipment::query()->create(['name' => $equipment])->id;
                    }
                }
                $service->equipments()->sync($validated['equipments']);
            }

            // staff
            $service->staffs()->sync($validated['staffs']);

            // procedures
            if (isset($validated['procedures'])) {
                $procedureData = [];

                foreach ($validated['procedures'] as $index => $procedure) {
                    if (!is_numeric($procedure)) {
                        $procedureModel = Procedure::firstOrCreate(['name' => trim($procedure)]);
                    } else {
                        $procedureModel = Procedure::findOrFail($procedure);
                    }

                    $procedureData[$procedureModel->id] = [
                        'procedure_date' => $validated['procedure_dates'][$index]
                    ];
                }

                $service->procedures()->sync($procedureData);
            }

            return $service;
        });

        return $service;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(PatientService $service, array $validated): PatientService
    {
        $service = DB::transaction(function () use ($validated, $service) {
            if (isset($validated['institution_id']) && !is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $service->update($validated);

            //  methods
            if (isset($validated['methods'])) {
                foreach ($validated['methods'] as $key => $method) {
                    if (!is_numeric($method)) {
                        $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
                    }
                }

                $service->methods()->sync($validated['methods']);
            }

            //  equipments
            if (isset($validated['equipments'])) {
                foreach ($validated['equipments'] as $key => $equipment) {
                    if (!is_numeric($equipment)) {
                        $validated['equipments'][$key] = Equipment::query()->create(['name' => $equipment])->id;
                    }
                }
                $service->equipments()->sync($validated['equipments']);
            }

            // staff
            $service->staffs()->sync($validated['staffs']);

            // procedures
            if (isset($validated['procedures'])) {
                $procedureData = [];

                foreach ($validated['procedures'] as $index => $procedure) {
                    if (!is_numeric($procedure)) {
                        $procedureModel = Procedure::firstOrCreate(['name' => trim($procedure)]);
                    } else {
                        $procedureModel = Procedure::findOrFail($procedure);
                    }

                    $procedureData[$procedureModel->id] = [
                        'procedure_date' => $validated['procedure_dates'][$index]
                    ];
                }

                $service->procedures()->sync($procedureData);
            }

            return $service;
        });

        return $service;
    }

    /**
     * @param PatientService $service
     * @return void
     */
    public function destroy(PatientService $service): void
    {
        $service->delete();
    }
}
