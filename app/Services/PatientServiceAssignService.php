<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientService;
use Illuminate\Support\Facades\DB;

class PatientServiceAssignService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): PatientService
    {
        $service = DB::transaction(function () use ($validated) {

            $map = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', true);
            if ($map->exists()) {
                $validated['examination_map_id'] = $map->first()->id;
            }

            $service = PatientService::query()->create($validated);

            //  methods
//            if (isset($validated['methods'])) {
//                foreach ($validated['methods'] as $key => $method) {
//                    if (!is_numeric($method)) {
//                        $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
//                    }
//                }
//
//                $service->methods()->sync($validated['methods']);
//            }


            //  equipments
//            if (isset($validated['equipments'])) {
//                foreach ($validated['equipments'] as $key => $equipment) {
//                    if (!is_numeric($equipment)) {
//                        $validated['equipments'][$key] = Equipment::query()->create(['name' => $equipment])->id;
//                    }
//                }
//                $service->equipments()->sync($validated['equipments']);
//            }

            // staff
//            $service->staffs()->sync($validated['staffs']);

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
            $service->update($validated);

            //  methods
//            if (isset($validated['methods'])) {
//                foreach ($validated['methods'] as $key => $method) {
//                    if (!is_numeric($method)) {
//                        $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
//                    }
//                }
//
//                $service->methods()->sync($validated['methods']);
//            }

            //  equipments
//            if (isset($validated['equipments'])) {
//                foreach ($validated['equipments'] as $key => $equipment) {
//                    if (!is_numeric($equipment)) {
//                        $validated['equipments'][$key] = Equipment::query()->create(['name' => $equipment])->id;
//                    }
//                }
//                $service->equipments()->sync($validated['equipments']);
//            }

            // staff
//            $service->staffs()->sync($validated['staffs']);

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
