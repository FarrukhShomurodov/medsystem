<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use App\Services\EquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EquipmentController
{
    protected EquipmentService $service;

    public function __construct(EquipmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $equipments = Equipment::query()
            ->select(['id', 'name', 'status'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.equipments.index', compact('equipments'));
    }

    public function create(): View
    {
        return view('admin.equipments.create');
    }

    public function store(EquipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('equipment.index')->with('success', 'Обородувание успешно добавлена!');
    }

    public function edit(Equipment $equipment): View
    {
        return view('admin.equipments.edit', compact('equipment'));
    }

    public function update(EquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($equipment, (array)$validated);

        return redirect()->route('equipment.index')->with('success', 'Обородувание успешно обновлена!');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->service->destroy($equipment);

        return redirect()->route('equipment.index')->with('success', 'Обородувание успешно удалена!');
    }
}
