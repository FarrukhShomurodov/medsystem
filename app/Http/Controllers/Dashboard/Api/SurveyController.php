<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Survey;
use Illuminate\Http\JsonResponse;

class SurveyController
{
    public function getQuestions(Survey $survey): JsonResponse
    {
        $groups = $survey->groups()->with('questions')->get();
        $standaloneQuestions = $survey->questions()
            ->whereNull('group_id')
            ->get();

        return response()->json([
            'groups' => $groups,
            'standalone_questions' => $standaloneQuestions
        ]);
    }
}
