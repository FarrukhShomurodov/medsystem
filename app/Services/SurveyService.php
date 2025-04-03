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

            foreach ($validated['groups'] ?? [] as $groupData) {
                $group = $survey->groups()->create([
                    'title' => $groupData['title'],
                ]);

                foreach ($groupData['questions'] ?? [] as $questionData) {
                    $group->questions()->create($questionData + ['survey_id' => $survey->id]);
                }
            }

            foreach ($validated['questions'] as $questionData) {
                if (!isset($questionData['group_id'])) {
                    $survey->questions()->create($questionData);
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
        return  DB::transaction(function () use ($survey, $validated) {
            $survey->update($validated);

            $survey->groups()->delete();
            $survey->questions()->whereNull('group_id')->delete();

            foreach ($validated['groups'] ?? [] as $groupData) {
                $group = $survey->groups()->create(['title' => $groupData['title']]);

                foreach ($groupData['questions'] ?? [] as $questionData) {
                    $group->questions()->create($questionData + ['survey_id' => $survey->id]);
                }
            }

            foreach ($validated['questions'] as $questionData) {
                if (!isset($questionData['group_id'])) {
                    $survey->questions()->create($questionData);
                }
            }
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
