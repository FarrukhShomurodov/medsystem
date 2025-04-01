<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\SurveyResponses;
use Illuminate\Http\JsonResponse;

class SurveyResponseController
{
    public function show(SurveyResponses $response): JsonResponse
    {
        $response->load('survey', 'answers.question');
        return response()->json($response);
    }
}
