<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ServiceStaffReqeust;
use App\Models\ServiceStaff;
use App\Models\ServiceStaffSpecialty;
use App\Services\ServiceStaffService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceStaffController
{
    private ServiceStaffService $service;

    public function __construct(ServiceStaffService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $staffs = ServiceStaff::query()
            ->select(['id', 'first_name', 'last_name', 'middle_name', 'service_staff_specialty_id'])
            ->with('specialty')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_staffs.index', compact('staffs'));
    }

    public function create(): View
    {
        $specialties = ServiceStaffSpecialty::query()->get(['id', 'name']);
        return view('admin.service_staffs.create', compact('specialties'));
    }

    public function store(ServiceStaffReqeust $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.staffs.index')->with('success', 'Персонал успешно добавлен!');
    }

    public function edit(ServiceStaff $staff): View
    {
        $specialties = ServiceStaffSpecialty::query()->get(['id', 'name']);
        return view('admin.service_staffs.edit', compact('staff', 'specialties'));
    }

    public function update(ServiceStaffReqeust $request, ServiceStaff $staff): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($staff, (array)$validated);

        return redirect()->route('service.staffs.index')->with('success', 'Персонал успешно обновлен!');
    }

    public function destroy(ServiceStaff $staff): RedirectResponse
    {
        $this->service->destroy($staff);

        return redirect()->route('service.staffs.index')->with('success', 'Персонал успешно удален!');
    }
}
