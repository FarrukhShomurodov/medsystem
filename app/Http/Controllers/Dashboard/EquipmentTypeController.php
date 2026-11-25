<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\EquipmentTypeRequest;
use App\Models\EquipmentType;
use App\Services\EquipmentTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class EquipmentTypeController extends Controller implements HasMiddleware
{
    public function __construct(private readonly EquipmentTypeService $service) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_equipment_types', only: ['index']),
            new Middleware('permission:create_equipment_types', only: ['create', 'store']),
            new Middleware('permission:edit_equipment_types', only: ['edit', 'update']),
            new Middleware('permission:delete_equipment_types', only: ['destroy']),
        ];
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
        $this->service->store($request->validated());

        return to_route('equipment.types.index')->with('success', 'Тип оборудования успешно добавлен!');
    }

    public function edit(EquipmentType $type): View
    {
        return view('admin.equipment_types.edit', compact('type'));
    }

    public function update(EquipmentTypeRequest $request, EquipmentType $type): RedirectResponse
    {
        $this->service->update($type, $request->validated());

        return to_route('equipment.types.index')->with('success', 'Тип оборудования успешно обновлен!');
    }

    public function destroy(EquipmentType $type): RedirectResponse
    {
        $this->service->destroy($type);

        return to_route('equipment.types.index')->with('success', 'Тип оборудования успешно удален!');
    }
}
