<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Requests\ServiceEquipmentRequest;
use App\Models\ServiceEquipment;
use App\Services\ServicEquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ServiceEquipmentController
{
    protected ServicEquipmentService $service;

    public function __construct(ServicEquipmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        $equipments = ServiceEquipment::query()
            ->select(['id', 'name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.service_equipments.index', compact('equipments'));
    }

    public function create(): View
    {
        return view('admin.service_equipments.create');
    }

    public function store(ServiceEquipmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('service.equipments.index')->with('success', 'Оборудования успешно добавлена!');
    }

    public function edit(ServiceEquipment $equipment): View
    {
        return view('admin.service_equipments.edit', compact('equipment'));
    }

    public function update(ServiceEquipmentRequest $request, ServiceEquipment $equipment): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($equipment, (array)$validated);

        return redirect()->route('service.equipments.index')->with('success', 'Оборудования успешно обновлена!');
    }

    public function destroy(ServiceEquipment $equipment): RedirectResponse
    {
        $this->service->destroy($equipment);

        return redirect()->route('service.equipments.index')->with('success', 'Оборудования успешно удалена!');
    }
}
