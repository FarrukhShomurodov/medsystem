<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyType;
use Illuminate\Support\Facades\DB;

class SurveyService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): Survey
    {

        $survey = DB::transaction(function () use ($validated) {
            if (!is_numeric($validated['survey_type_id'])) {
                $validated['survey_type_id'] = SurveyType::query()->create(['name' => $validated['survey_type_id']])->id;
            }

            $survey = Survey::query()->create($validated);

            foreach ($validated['questions'] as $question) {
                $survey->questions()->create($question);
            }

            foreach ($validated['groups'] ?? [] as $group) {
                $groupModel = $survey->groups()->create(['name' => $group['name']]);
                foreach ($group['questions'] as $question) {
                    $groupModel->questions()->create($question);
                }
            }

            return $survey;
        });

        return $survey;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(Survey $survey, array $validated): Survey
    {
        return DB::transaction(function () use ($survey, $validated) {
            if (!is_numeric($validated['survey_type_id'])) {
                $validated['survey_type_id'] = SurveyType::query()->create(['name' => $validated['survey_type_id']])->id;
            }

            $survey->update($validated);

            $existingQuestionIds = collect($validated['questions'])->pluck('id')->filter();
            $survey->questions()->whereNotIn('id', $existingQuestionIds)->delete();

            foreach ($validated['questions'] as $question) {
                if (isset($question['id'])) {
                    $survey->questions()->where('id', $question['id'])->update($question);
                } else {
                    $survey->questions()->create($question);
                }
            }

            return $survey->refresh();
        });
    }

    /**
     * @param Survey $survey
     * @return void
     */
    public function destroy(Survey $survey): void
    {
        $survey->delete();
    }
}
