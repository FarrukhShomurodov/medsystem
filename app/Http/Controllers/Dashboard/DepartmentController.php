<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\DepartmentType;
use App\Models\Service;
use App\Services\DepartmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentController
{
    protected DepartmentService $service;

    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $departments = Department::query()
            ->select(['id', 'name', 'type_id'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        $types = DepartmentType::query()->get();
        $services = Service::query()->get();
        $equipments = Equipment::query()->get();
        $departments = Department::query()->get();

        return view('admin.departments.create', compact('types', 'services', 'departments', 'equipments'));
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно добавлен!');
    }

    public function edit(Department $department): View
    {
        $types = DepartmentType::query()->get();
        $services = Service::query()->get();
        $equipments = Equipment::query()->get();
        $departments = Department::query()->get();

        return view('admin.departments.edit', compact('department', 'types', 'services', 'departments', 'equipments'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($department, (array)$validated);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно обновлен!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->service->destroy($department);

        return redirect()->route('departments.index')->with('success', 'Отделение успешно удален!');
    }
}
