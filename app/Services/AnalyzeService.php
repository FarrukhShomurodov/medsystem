<?php

namespace App\Services;

use App\Models\AnalyzeMethod;
use App\Models\AnalyzeType;
use App\Models\Laboratory;
use App\Models\Analyze;
use App\Models\MedicalEquipment;
use Illuminate\Support\Facades\DB;

class AnalyzeService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Analyze
    {
        $analyze = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['laboratory_id'])) {
                $validated['laboratory_id'] = Laboratory::query()->create(['name' => $validated['laboratory_id']])->id;
            }

            $analyze = Analyze::query()->create($validated);

            //  Medical devices
            foreach ($validated['methods'] as $key => $method) {
                if (!is_numeric($method)) {
                    $validated['methods'][$key] = AnalyzeMethod::query()->create(['name' => $method])->id;
                }
            }

            $analyze->methods()->sync($validated['methods']);

            //  Types
            foreach ($validated['types'] as $key => $type) {
                if (!is_numeric($type)) {
                    $validated['types'][$key] = AnalyzeType::query()->create(['name' => $type])->id;
                }
            }

            $analyze->types()->sync($validated['types']);

            //  Medical equipments
            foreach ($validated['equipments'] as $key => $equipment) {
                if (!is_numeric($equipment)) {
                    $validated['equipments'][$key] = MedicalEquipment::query()->create(['name' => $equipment])->id;
                }
            }
            $analyze->equipments()->sync($validated['equipments']);

            return $analyze;
        });

        return $analyze;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Analyze $analyze, array $validated): Analyze
    {
        $analyze = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['laboratory_id'])) {
                $validated['laboratory_id'] = Laboratory::query()->create(['name' => $validated['laboratory_id']])->id;
            }

            $analyze = Analyze::query()->update($validated);

            //  Medical devices
            foreach ($validated['methods'] as $key => $method) {
                if (!is_numeric($method)) {
                    $validated['methods'][$key] = AnalyzeMethod::query()->create(['name' => $method])->id;
                }
            }

            $analyze->methods()->sync($validated['methods']);

            //  Types
            foreach ($validated['types'] as $key => $type) {
                if (!is_numeric($type)) {
                    $validated['types'][$key] = AnalyzeType::query()->create(['name' => $type])->id;
                }
            }

            $analyze->types()->sync($validated['types']);

            //  Medical equipments
            foreach ($validated['equipments'] as $key => $equipment) {
                if (!is_numeric($equipment)) {
                    $validated['equipments'][$key] = MedicalEquipment::query()->create(['name' => $equipment])->id;
                }
            }
            $analyze->equipments()->sync($validated['equipments']);

            return $analyze;
        });

        return $analyze;
    }

    /**
     * @param Analyze $analyze
     * @return void
     */
    public function destroy(Analyze $analyze): void
    {
        $analyze->delete();
    }
}
