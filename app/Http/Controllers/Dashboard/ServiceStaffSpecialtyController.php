<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\ServiceStaffSpecialtyRequest;
use App\Models\ServiceStaffSpecialty;
use App\Services\ServiceStaffSpecialtyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceStaffSpecialtyController
{
    protected ServiceStaffSpecialtyService $service;

    public function __construct(ServiceStaffSpecialtyService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $specialties = ServiceStaffSpecialty::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_staff_specialties.index', compact('specialties'));
    }

    public function create(): View
    {
        return view('admin.service_staff_specialties.create');
    }

    public function store(ServiceStaffSpecialtyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.staff.specialties.index')->with('success', 'Специальность успешно добавлена!');
    }

    public function edit(ServiceStaffSpecialty $specialty): View
    {
        return view('admin.service_staff_specialties.edit', compact('specialty'));
    }

    public function update(ServiceStaffSpecialtyRequest $request, ServiceStaffSpecialty $specialty): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($specialty, (array)$validated);

        return redirect()->route('service.staff.specialties.index')->with('success', 'Специальность успешно обновлена!');
    }

    public function destroy(ServiceStaffSpecialty $specialty): RedirectResponse
    {
        $this->service->destroy($specialty);

        return redirect()->route('service.staff.specialties.index')->with('success', 'Специальность успешно удалена!');
    }
}
