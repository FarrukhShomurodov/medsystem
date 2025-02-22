<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\DepartmentEquipmentRequest;
use App\Models\DepartmentEquipment;
use App\Services\DepartmentEquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DepartmentEquipmentController
{
    protected DepartmentEquipmentService $service;

    public function __construct(DepartmentEquipmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $equipments = DepartmentEquipment::query()
            ->select(['id', 'name', 'status'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.department_equipments.index', compact('equipments'));
    }

    public function create(): View
    {
        return view('admin.department_equipments.create');
    }

    public function store(DepartmentEquipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('departments.equipment.index')->with('success', 'Обородувание успешно добавлена!');
    }

    public function edit(DepartmentEquipment $equipment): View
    {
        return view('admin.department_equipments.edit', compact('equipment'));
    }

    public function update(DepartmentEquipmentRequest $request, DepartmentEquipment $equipment): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($equipment, (array)$validated);

        return redirect()->route('departments.equipment.index')->with('success', 'Обородувание успешно обновлена!');
    }

    public function destroy(DepartmentEquipment $equipment): RedirectResponse
    {
        $this->service->destroy($equipment);

        return redirect()->route('departments.equipment.index')->with('success', 'Обородувание успешно удалена!');
    }
}
