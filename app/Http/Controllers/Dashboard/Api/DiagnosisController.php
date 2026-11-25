<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\DiagnosisSearchRequest;
use App\Models\Admin;
use App\Models\Diagnosis;
use App\Queries\Patients\DiagnosisPickerQuery;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

class DiagnosisController extends Controller
{
    /**
     * The diagnosis picker of the patient card: the catalogue entries matching the typed text,
     * the signed-in account's favourites left out (the card lists them).
     */
    public function search(DiagnosisSearchRequest $request, DiagnosisPickerQuery $diagnoses, #[CurrentUser(Admin::GUARD)] Admin $viewer): JsonResponse
    {
        return response()->json($diagnoses->search($viewer, $request->term())->map(fn (Diagnosis $diagnosis): array => [
            'id' => $diagnosis->id,
            'code' => $diagnosis->code,
            'name' => $diagnosis->name,
        ]));
    }
}
