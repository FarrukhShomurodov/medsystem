<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Models\SurveyResponse;
use Illuminate\Http\JsonResponse;

class SurveyResponseController extends Controller
{
    public function show(SurveyResponse $response): JsonResponse
    {
        return response()->json($response->load(['survey', 'answers.question']));
    }
}
