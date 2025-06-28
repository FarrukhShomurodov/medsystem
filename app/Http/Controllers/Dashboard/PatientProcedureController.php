<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientProcedureRequest;
use App\Services\PatientProcedureService;
use Illuminate\Http\RedirectResponse;

class PatientProcedureController extends Controller
{
    protected PatientProcedureService $service;

    public function __construct(PatientProcedureService $service)
    {
        $this->service = $service;
    }

    function store(PatientProcedureRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->store($validated);

        return redirect()->back();
    }
}
