<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentRequest;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Manufacturer;
use App\Models\Staff;
use App\Services\EquipmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EquipmentController extends Controller implements HasMiddleware
{
    public function __construct(private readonly EquipmentService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_equipments', only: ['index']),
            new Middleware('permission:create_equipments', only: ['create', 'store']),
            new Middleware('permission:edit_equipments', only: ['edit', 'update']),
            new Middleware('permission:delete_equipments', only: ['destroy']),
        ];
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
        $equipmentTypes = EquipmentType::query()->get();
        $manufacturers = Manufacturer::query()->get();
        $staffList = Staff::query()->get();

        return view('admin.equipments.create', compact('equipmentTypes', 'manufacturers', 'staffList'));
    }

    public function store(EquipmentRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('equipment.index')->with('success', 'Оборудование успешно добавлено!');
    }

    public function edit(Equipment $equipment): View
    {
        $equipmentTypes = EquipmentType::query()->get();
        $manufacturers = Manufacturer::query()->get();
        $staffList = Staff::query()->get();

        return view('admin.equipments.edit', compact('equipment', 'equipmentTypes', 'manufacturers', 'staffList'));
    }

    public function update(EquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $this->service->update($equipment, $request->validated());

        return to_route('equipment.index')->with('success', 'Оборудование успешно обновлено!');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->service->destroy($equipment);

        return to_route('equipment.index')->with('success', 'Оборудование успешно удалено!');
    }
}
