<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\DB;

class SurveyResponseService
{
    /**
     * The response belongs to the examination map it was filled in on, or else to the patient's
     * active map, if any.
     *
     * @param  array{survey_id: int, patient_id: int, examination_map_id?: int|null, patient_service_id?: int|null, answers: array<int|string, string>}  $validated
     */
    public function store(array $validated): SurveyResponse
    {
        $patient = Patient::query()->findOrFail($validated['patient_id']);
        $mapId = $validated['examination_map_id'] ?? $patient->activeExaminationMap()->value('id');

        return DB::transaction(function () use ($validated, $patient, $mapId): SurveyResponse {
            $response = SurveyResponse::query()->create([
                'patient_id' => $patient->id,
                'examination_map_id' => $mapId,
                'patient_service_id' => $validated['patient_service_id'] ?? null,
                'survey_id' => $validated['survey_id'],
            ]);

            $response->answers()->createMany(
                collect($validated['answers'])
                    ->map(fn (string $answer, int|string $questionId): array => ['question_id' => (int) $questionId, 'answer' => $answer])
                    ->values()
                    ->all(),
            );

            return $response;
        });
    }
}
