<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientInspectionRequest;
use App\Services\PatientInspectionService;
use Illuminate\Http\RedirectResponse;

class PatientInspectionController extends Controller
{
    public function __construct(private readonly PatientInspectionService $service) {}

    public function store(PatientInspectionRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return back()->with('success', 'Осмотр добавлен!');
    }
}
