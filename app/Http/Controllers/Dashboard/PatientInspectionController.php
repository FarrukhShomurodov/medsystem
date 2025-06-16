<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientInspectionRequest;
use App\Services\PatientInspectionService;
use Illuminate\Http\RedirectResponse;

class PatientInspectionController extends Controller
{
    protected PatientInspectionService $service;

    public function __construct(PatientInspectionService $service)
    {
        $this->service = $service;
    }

    public function store(PatientInspectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->back()->with('success', 'Осмотр добавлен!');
    }
}
