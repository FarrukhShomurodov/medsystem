<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\MedicalEquipmentRequest;
use App\Models\MedicalEquipment;
use App\Services\MedicalEquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MedicalEquipmentController
{
    protected MedicalEquipmentService $service;

    public function __construct(MedicalEquipmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $equipments = MedicalEquipment::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.equipments.index', compact('equipments'));
    }

    public function create(): View
    {
        return view('admin.equipments.create');
    }

    public function store(MedicalEquipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('equipments.index')->with('success', 'Оборудования успешно добавлена!');
    }

    public function edit(MedicalEquipment $equipment): View
    {
        return view('admin.equipments.edit', compact('equipment'));
    }

    public function update(MedicalEquipmentRequest $request, MedicalEquipment $equipment): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($equipment, (array)$validated);

        return redirect()->route('equipments.index')->with('success', 'Оборудования успешно обновлена!');
    }

    public function destroy(MedicalEquipment $equipment): RedirectResponse
    {
        $this->service->destroy($equipment);

        return redirect()->route('equipments.index')->with('success', 'Оборудования успешно удалена!');
    }
}
