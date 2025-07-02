<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientDocumentScoreRequest;
use App\Models\PatientDocumentScoreFinished;
use Illuminate\Http\JsonResponse;

class ScoreFinishedController extends Controller
{
    public function finish(StorePatientDocumentScoreRequest $request): JsonResponse
    {
        $record = PatientDocumentScoreFinished::storeFromRequest((array)$request->validated());

        return response()->json($record);
    }
}
