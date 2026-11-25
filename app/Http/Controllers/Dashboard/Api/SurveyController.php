<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;

class SurveyController extends Controller
{
    public function getQuestions(Survey $survey): JsonResponse
    {
        return response()->json([
            'groups' => $survey->groups()->with('questions')->get(),
            'standalone_questions' => $survey->questions()->whereNull('group_id')->get(),
        ]);
    }
}
