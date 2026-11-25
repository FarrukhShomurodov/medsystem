<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\StorePatientDocumentScoreRequest;
use App\Models\PatientDocumentScoreFinished;
use Illuminate\Http\JsonResponse;

class ScoreFinishedController extends Controller
{
    public function finish(StorePatientDocumentScoreRequest $request): JsonResponse
    {
        return response()->json(PatientDocumentScoreFinished::markDone($request->scoreAction()));
    }
}
