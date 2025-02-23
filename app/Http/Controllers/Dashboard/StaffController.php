<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\StaffRequest;
use App\Models\Department;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\StaffService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class StaffController
{
    protected StaffService $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $staffs = Staff::query()
            ->select(['id', 'name', 'last_name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create(): View
    {
        $positions = StaffPosition::query()->get();
        $specialties = Specialty::query()->get();
        $departments = Department::query()->get();
        $services = Service::query()->get();

        return view('admin.staffs.create', compact('specialties', 'positions', 'departments', 'services'));
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно добавлен!');
    }

    public function edit(Staff $staff): View
    {
        $positions = StaffPosition::query()->get();
        $specialties = Specialty::query()->get();
        $departments = Department::query()->get();
        $services = Service::query()->get();

        return view('admin.staffs.edit', compact('staff', 'specialties', 'positions', 'departments', 'services'));
    }

    public function update(StaffRequest $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($staff, (array)$validated);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно обновлен!');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->service->destroy($staff);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно удален!');
    }
}
