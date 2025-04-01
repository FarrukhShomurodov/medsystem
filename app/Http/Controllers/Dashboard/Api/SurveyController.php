<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Survey;
use Illuminate\Http\JsonResponse;

class SurveyController
{
    public function getQuestions(Survey $survey): JsonResponse
    {
        return response()->json($survey->questions);
    }
}
