<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SelectedDiagnosisRequest;
use App\Models\Admin;
use App\Services\AdminService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

class SelectedDiagnosisController extends Controller
{
    public function __construct(private readonly AdminService $service) {}

    /**
     * Adds the diagnosis to the favourites of the signed-in admin, or removes it when it is there.
     */
    public function store(SelectedDiagnosisRequest $request, #[CurrentUser(Admin::GUARD)] Admin $admin): JsonResponse
    {
        $this->service->toggleSelectedDiagnosis($admin, $request->integer('diagnosis_id'));

        return response()->json(['message' => 'Избранные диагнозы обновлены.']);
    }
}
