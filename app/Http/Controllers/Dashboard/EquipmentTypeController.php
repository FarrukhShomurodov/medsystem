<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentTypeRequest;
use App\Models\EquipmentType;
use App\Services\EquipmentTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EquipmentTypeController extends Controller
{
    protected EquipmentTypeService $service;

    public function __construct(EquipmentTypeService $service)
    {
        $this->middleware(['permission:view_equipment_types'])->only('index');
        $this->middleware(['permission:create_equipment_types'])->only(['create', 'store']);
        $this->middleware(['permission:edit_equipment_types'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_equipment_types'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $types = EquipmentType::query()->select(['id', 'name'])
            ->orderBy('id')
            ->paginate(10);

        return view('admin.equipment_types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.equipment_types.create');
    }

    public function store(EquipmentTypeRequest $request): RedirectResponse
    {
        $this->service->store((array)$request->validated());

        return redirect()->route('equipment.types.index')->with('success', 'Админ успешно добавлен!');
    }

    public function edit(EquipmentType $type): View
    {
        return view('admin.equipment_types.edit', compact('type'));
    }

    public function update(EquipmentTypeRequest $request, EquipmentType $type): RedirectResponse
    {
        $this->service->update($type, (array)$request->validated());

        return redirect()->route('equipment.types.index')->with('success', 'Админ успешно обновлен!');
    }

    public function destroy(EquipmentType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return redirect()->route('equipment.types.index')->with('success', 'Админ успешно удален!');
    }
}
