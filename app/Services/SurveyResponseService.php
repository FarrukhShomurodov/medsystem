<?php

namespace App\Services;

use App\Models\SurveyResponses;
use Illuminate\Support\Facades\DB;

class SurveyResponseService
{
    private PatientServiceAssignService $patientService;

    public function __construct(AnalyzeService $service, PatientServiceAssignService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): SurveyResponses
    {
        $surveyResponse = DB::transaction(function () use ($validated) {
            $surveyResponse = SurveyResponses::query()->create($validated);

            foreach ($validated['answers'] as $index => $answer) {
                $surveyResponse->answers()->create([
                    'question_id' => $index,
                    'answer' => $answer,
                ]);
            }

            $map = $surveyResponse->map;
            $patient = $map->patient;

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

            return $surveyResponse;
        });

        return $surveyResponse;
    }
}
