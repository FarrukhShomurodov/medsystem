<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use App\Services\EquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EquipmentController extends Controller
{
    protected EquipmentService $service;

    public function __construct(EquipmentService $service)
    {
        $this->middleware(['permission:view_equipments'])->only('index');
        $this->middleware(['permission:create_equipments'])->only(['create', 'store']);
        $this->middleware(['permission:edit_equipments'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_equipments'])->only('destroy');

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
