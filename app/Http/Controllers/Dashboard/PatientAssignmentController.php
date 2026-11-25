<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientAssignmentRequest;
use App\Models\Admin;
use App\Services\PatientAssignmentService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;

class PatientAssignmentController extends Controller
{
    public function __construct(private readonly PatientAssignmentService $service) {}

    public function store(PatientAssignmentRequest $request, #[CurrentUser(Admin::GUARD)] Admin $author): RedirectResponse
    {
        $assignment = $this->service->store($request->assignment(), $author);

        return to_route('patients.show', $assignment->patient_id)
            ->with('success', 'Направление успешно создано!');
    }
}
