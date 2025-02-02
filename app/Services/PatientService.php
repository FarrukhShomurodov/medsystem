<?php

namespace App\Services;

use App\Models\Allegory;
use App\Models\ChronicDisease;
use App\Models\City;
use App\Models\Institution;
use App\Models\MedicalDevice;
use App\Models\Patient;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class PatientService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Patient
    {
        $patient = DB::transaction(function () use ($validated) {

            if (!is_numeric($validated['region_id'])) {
                $validated['region_id'] = Region::query()->create(['name' => $validated['region_id']])->id;
            }

            if (!is_numeric($validated['city_id'])) {
                /** @var array $validated */
                $validated['city_id'] = City::query()->create([
                    'name' => $validated['city_id'],
                    'region_id' => $validated['region_id']
                ])->id;
            }

            if (isset($validated['registered_region_id']) && !is_numeric($validated['registered_region_id'])) {
                $validated['registered_region_id'] = Region::query()->create(['name' => $validated['registered_region_id']])->id;
            }

            if (isset($validated['registered_city_id']) && !is_numeric($validated['registered_city_id'])) {
                /** @var array $validated */
                $validated['registered_city_id'] = City::query()->create([
                    'name' => $validated['city_id'],
                    'region_id' => $validated['registered_region_id']
                ])->id;
            }

            $patient = Patient::query()->create($validated);

            //  Allegories
            foreach ($validated['allegories'] as $key => $allegory) {
                if (!is_numeric($allegory)) {
                    $validated['allegories'][$key] = Allegory::query()->create(['name' => $allegory])->id;
                }
            }

            $patient->allegories()->sync($validated['allegories']);

            //  Medical devices
            foreach ($validated['medical_devices'] as $key => $device) {
                if (!is_numeric($device)) {
                    $validated['medical_devices'][$key] = MedicalDevice::query()->create(['name' => $device])->id;
                }
            }

            $patient->devices()->sync($validated['medical_devices']);

            //  Chronic diseases
            foreach ($validated['chronic_diseases'] as $key => $disease) {
                if (!is_numeric($disease)) {
                    $validated['chronic_diseases'][$key] = ChronicDisease::query()->create(['name' => $disease])->id;
                }
            }

            $patient->diseases()->sync($validated['chronic_diseases']);


            // Examination Map
            if (!is_numeric($validated['institution_id'])) {
                $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
            }

            $mapData = [
                'institution_id' => $validated['institution_id'],
                'doctor_id' => $validated['doctor_id'],
                'admission_date' => $validated['admission_date'],
            ];

            $patient->examinationMaps()->create($mapData);

            return $patient;
        });


        return $patient;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Patient $patient, array $validated): Patient
    {
        $patient = DB::transaction(function () use ($validated, $patient) {

            if (!is_numeric($validated['region_id'])) {
                $validated['region_id'] = Region::query()->create(['name' => $validated['region_id']])->id;
            }

            if (!is_numeric($validated['city_id'])) {
                /** @var array $validated */
                $validated['city_id'] = City::query()->create([
                    'name' => $validated['city_id'],
                    'region_id' => $validated['region_id']
                ])->id;
            }

            if (isset($validated['registered_region_id']) && !is_numeric($validated['registered_region_id'])) {
                $validated['registered_region_id'] = Region::query()->create(['name' => $validated['registered_region_id']])->id;
            }

            if (isset($validated['registered_city_id']) && !is_numeric($validated['registered_city_id'])) {
                /** @var array $validated */
                $validated['registered_city_id'] = City::query()->create([
                    'name' => $validated['city_id'],
                    'region_id' => $validated['registered_region_id']
                ])->id;
            }

            $patient->update($validated);

            //  Allegories
            foreach ($validated['allegories'] as $key => $allegory) {
                if (!is_numeric($allegory)) {
                    $validated['allegories'][$key] = Allegory::query()->create(['name' => $allegory])->id;
                }
            }

            $patient->allegories()->sync($validated['allegories']);

            //  Medical devices
            foreach ($validated['medical_devices'] as $key => $device) {
                if (!is_numeric($device)) {
                    $validated['medical_devices'][$key] = MedicalDevice::query()->create(['name' => $device])->id;
                }
            }

            $patient->devices()->sync($validated['medical_devices']);

            //  Chronic diseases
            foreach ($validated['chronic_diseases'] as $key => $disease) {
                if (!is_numeric($disease)) {
                    $validated['chronic_diseases'][$key] = ChronicDisease::query()->create(['name' => $disease])->id;
                }
            }

            $patient->diseases()->sync($validated['chronic_diseases']);

            // Examination Map
            if (isset($validated['institution_id']) && isset($validated['doctor_id']) && isset($validated['admission_date'])) {
                if (!is_numeric($validated['institution_id'])) {
                    $validated['institution_id'] = Institution::query()->create(['name' => $validated['institution_id']])->id;
                }

                $mapData = [
                    'institution_id' => $validated['institution_id'],
                    'doctor_id' => $validated['doctor_id'],
                    'admission_date' => $validated['admission_date'],
                ];

                $patient->examinationMaps()->create($mapData);
            }

            return $patient;
        });

        return $patient->refresh();
    }

    /**
     * @param Patient $patient
     * @return void
     */
    public function destroy(Patient $patient): void
    {
        $patient->delete();
    }
}
