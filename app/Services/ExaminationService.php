<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\ExaminationMap;
use Illuminate\Support\Facades\DB;

class ExaminationService
{
    private AnalyzeService $service;
    private PatientServiceAssignService $patientService;
    private SurveyResponseService $surveyResponseService;

    public function __construct(AnalyzeService $service, PatientServiceAssignService $patientService, SurveyResponseService $surveyResponseService)
    {
        $this->service = $service;
        $this->patientService = $patientService;
        $this->surveyResponseService = $surveyResponseService;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): ExaminationMap
    {
        $map = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['department_id'])) {
                $validated['department_id'] = Institution::query()->create(['name' => $validated['department_id']])->id;
            }

            $map = ExaminationMap::query()->create($validated);

            $patient = $map->patient;

            if (isset($validated['laboratory_id'])) {
                $analyzeData = [
                    'patient_id' => $patient->id,
                    'staff_id' => $validated['staff_id'],
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


            if (isset($validated['service_id'])) {
                $serviceData = [
                    'service_id' => $validated['service_id'],
                    'patient_id' => $patient->id,
                    'examination_map_id' => $map->id,
                    'service_date' => $validated['service_date'],
                    'place' => $validated['place'],
                    'status' => $validated['service_status'],
                    'staffs' => $validated['staffs'],
                ];

                if (isset($validated['procedures'])) {
                    $serviceData['procedures'] = $validated['procedures'];
                    $serviceData['procedure_dates'] = $validated['procedure_dates'];
                }

                if (isset($validated['medications'])) {
                    $serviceData['medications'] = $validated['medications'];
                    $serviceData['dosages'] = $validated['dosages'];
                    $serviceData['admission_periods'] = $validated['admission_periods'];
                    $serviceData['medication_notes'] = $validated['medication_notes'];
                }

                $this->patientService->store($serviceData);
            }


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

            if (!is_numeric($validated['department_id'])) {
                $validated['department_id'] = Institution::query()->create(['name' => $validated['department_id']])->id;
            }

            $map->update($validated);

            $patient = $map->patient;

            if (isset($validated['laboratory_id'])) {
                $analyzeData = [
                    'patient_id' => $patient->id,
                    'staff_id' => $validated['staff_id'],
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

            if (isset($validated['service_id'])) {
                $serviceData = [
                    'service_id' => $validated['service_id'],
                    'patient_id' => $patient->id,
                    'examination_map_id' => $map->id,
                    'service_date' => $validated['service_date'],
                    'place' => $validated['place'],
                    'status' => $validated['service_status'],
                    'staffs' => $validated['staffs']
                ];

                if (isset($validated['procedures'])) {
                    $serviceData['procedures'] = $validated['procedures'];
                    $serviceData['procedure_dates'] = $validated['procedure_dates'];
                }

                if (isset($validated['medications'])) {
                    $serviceData['medications'] = $validated['medications'];
                    $serviceData['dosages'] = $validated['dosages'];
                    $serviceData['admission_periods'] = $validated['admission_periods'];
                    $serviceData['medication_notes'] = $validated['medication_notes'];
                }

                $this->patientService->store($serviceData);
            }


            if (isset($validated['discharge_date']) && $validated['discharge_date']) {
                $map->update([
                    'is_active' => false
                ]);
            }

            if (isset($validated['survey_id'])) {
                $analyzeData = [
                    'survey_id' => $validated['survey_id'],
                    'answers' => $validated['answers'],
                    'examination_map_id' => $map->id,
                ];

                $this->surveyResponseService->store($analyzeData);
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
