<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientProcedureRequest;
use App\Services\PatientProcedureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PatientProcedureController extends Controller implements HasMiddleware
{
    public function __construct(private readonly PatientProcedureService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:create_patient_procedure', only: ['store']),
        ];
    }

    public function store(PatientProcedureRequest $request): RedirectResponse
    {
        $this->service->store($request->orders());

        return back()->with('success', 'Процедуры назначены!');
    }
}
