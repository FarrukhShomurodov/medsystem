<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\PatientService;
use App\Models\ServiceEquipment;
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
            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $service = PatientService::query()->create($validated);

            //  methods
            foreach ($validated['methods'] as $key => $method) {
                if (!is_numeric($method)) {
                    $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
                }
            }

            $service->methods()->sync($validated['methods']);

            //  equipments
            foreach ($validated['equipments'] as $key => $equipment) {
                if (!is_numeric($equipment)) {
                    $validated['equipments'][$key] = ServiceEquipment::query()->create(['name' => $equipment])->id;
                }
            }
            $service->equipments()->sync($validated['equipments']);

            // staff
            $service->staffs()->sync($validated['equipments']);

            return $service;
        });

        return $service;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(PatientService $service, array $validated): PatientService
    {
        $service = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $service = PatientService::query()->update($validated);

            //  methods
            foreach ($validated['methods'] as $key => $method) {
                if (!is_numeric($method)) {
                    $validated['methods'][$key] = ServiceMethod::query()->create(['name' => $method])->id;
                }
            }

            $service->methods()->sync($validated['methods']);

            //  equipments
            foreach ($validated['equipments'] as $key => $equipment) {
                if (!is_numeric($equipment)) {
                    $validated['equipments'][$key] = ServiceEquipment::query()->create(['name' => $equipment])->id;
                }
            }
            $service->equipments()->sync($validated['equipments']);

            // staff
            $service->staffs()->sync($validated['equipments']);

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
