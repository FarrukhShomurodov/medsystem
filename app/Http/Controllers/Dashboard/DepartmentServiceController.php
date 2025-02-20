<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\DepartmentServiceRequest;
use App\Models\DepartmentService;
use App\Services\DepartmentMaintenanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentServiceController
{
    protected DepartmentMaintenanceService $service;

    public function __construct(DepartmentMaintenanceService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $services = DepartmentService::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.department_services.index', compact('services'));
    }

    public function create(): View
    {
        return view('admin.department_services.create');
    }

    public function store(DepartmentServiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.service.index')->with('success', 'Город успешно добавлен!');
    }

    public function edit(DepartmentService $service): View
    {
        return view('admin.department_services.edit', compact('service'));
    }

    public function update(DepartmentServiceRequest $request, DepartmentService $service): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($service, (array)$validated);

        return redirect()->route('departments.service.index')->with('success', 'Город успешно обновлен!');
    }

    public function destroy(DepartmentService $service): RedirectResponse
    {
        $this->service->destroy($service);

        return redirect()->route('departments.service.index')->with('success', 'Город успешно удален!');
    }
}
